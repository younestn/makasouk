<template>
  <div class="location-picker-map stack">
    <div class="location-picker-map__header">
      <div>
        <p class="label" style="margin: 0;">{{ t('maps.algeria_only_title') }}</p>
        <p class="small" style="margin: 0;">{{ t('maps.click_to_select') }}</p>
      </div>
      <span class="badge badge-info">{{ t('maps.algeria_badge') }}</span>
    </div>

    <div ref="mapElement" class="location-picker-map__canvas">
      <div v-if="loading" class="location-picker-map__loading">
        {{ t('maps.loading') }}
      </div>
    </div>

    <div v-if="mapError" class="alert alert-warning">{{ mapError }}</div>

    <div class="location-picker-map__summary">
      <span>{{ t('maps.selected_coordinates') }}</span>
      <strong>{{ formattedCoordinates }}</strong>
    </div>
  </div>
</template>

<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { fetchMapConfig } from '@/services/mapService';
import { useI18n } from '@/composables/useI18n';

const props = defineProps({
  latitude: {
    type: [Number, String, null],
    default: null,
  },
  longitude: {
    type: [Number, String, null],
    default: null,
  },
  wilayaOptions: {
    type: Array,
    default: () => [],
  },
});

const emit = defineEmits([
  'update:latitude',
  'update:longitude',
  'wilaya-suggested',
  'label-suggested',
  'error',
]);

const { t, locale } = useI18n();

const mapElement = ref(null);
const loading = ref(true);
const mapError = ref('');
const config = ref(null);

let leaflet = null;
let map = null;
let marker = null;
let tileLayer = null;

const numericLatitude = computed(() => toNumber(props.latitude));
const numericLongitude = computed(() => toNumber(props.longitude));

const formattedCoordinates = computed(() => {
  if (numericLatitude.value === null || numericLongitude.value === null) {
    return t('maps.no_point_selected');
  }

  return `${numericLatitude.value.toFixed(6)}, ${numericLongitude.value.toFixed(6)}`;
});

function toNumber(value) {
  if (value === null || value === undefined || value === '') {
    return null;
  }

  const parsed = Number(value);
  return Number.isFinite(parsed) ? parsed : null;
}

function algeriaBounds() {
  const bounds = config.value?.algeria?.bounds;

  return [
    [
      bounds?.south_west?.latitude ?? 18.9,
      bounds?.south_west?.longitude ?? -8.7,
    ],
    [
      bounds?.north_east?.latitude ?? 37.2,
      bounds?.north_east?.longitude ?? 12.1,
    ],
  ];
}

function defaultCenter() {
  return [
    config.value?.algeria?.center?.latitude ?? 28.0339,
    config.value?.algeria?.center?.longitude ?? 1.6596,
  ];
}

function selectedPoint() {
  if (numericLatitude.value === null || numericLongitude.value === null) {
    return null;
  }

  return [numericLatitude.value, numericLongitude.value];
}

function pointIsInsideAlgeria(lat, lng) {
  const [[south, west], [north, east]] = algeriaBounds();

  return lat >= south && lat <= north && lng >= west && lng <= east;
}

async function initializeMap() {
  loading.value = true;
  mapError.value = '';

  try {
    const response = await fetchMapConfig();
    config.value = response.data || {};
    leaflet = await import('leaflet');
    await nextTick();

    const bounds = leaflet.latLngBounds(algeriaBounds());
    map = leaflet.map(mapElement.value, {
      center: selectedPoint() || defaultCenter(),
      zoom: selectedPoint() ? Math.max(config.value?.algeria?.zoom ?? 5, 12) : (config.value?.algeria?.zoom ?? 5),
      minZoom: config.value?.algeria?.min_zoom ?? 5,
      maxZoom: config.value?.algeria?.max_zoom ?? 18,
      maxBounds: bounds,
      maxBoundsViscosity: 0.95,
      worldCopyJump: false,
    });

    tileLayer = leaflet.tileLayer(config.value.tile_url, {
      attribution: config.value.attribution || '&copy; OpenStreetMap contributors',
      bounds,
    }).addTo(map);

    if (!selectedPoint()) {
      map.fitBounds(bounds, { padding: [12, 12] });
    }

    if (selectedPoint()) {
      setMarker(numericLatitude.value, numericLongitude.value, false);
    }

    map.on('click', (event) => {
      selectPoint(event.latlng.lat, event.latlng.lng);
    });
  } catch (error) {
    mapError.value = t('maps.load_failed');
    emit('error', mapError.value);
  } finally {
    loading.value = false;
  }
}

function setMarker(lat, lng, pan = true) {
  if (!leaflet || !map) {
    return;
  }

  const latLng = [lat, lng];
  const icon = leaflet.divIcon({
    className: 'location-picker-map__marker',
    html: '<span></span>',
    iconSize: [28, 28],
    iconAnchor: [14, 14],
  });

  if (!marker) {
    marker = leaflet.marker(latLng, { icon, draggable: true }).addTo(map);
    marker.on('dragend', () => {
      const next = marker.getLatLng();
      selectPoint(next.lat, next.lng);
    });
  } else {
    marker.setLatLng(latLng);
  }

  if (pan) {
    map.setView(latLng, Math.max(map.getZoom(), 12), { animate: true });
  }
}

async function selectPoint(lat, lng) {
  const nextLatitude = Number(lat.toFixed(6));
  const nextLongitude = Number(lng.toFixed(6));

  if (!pointIsInsideAlgeria(nextLatitude, nextLongitude)) {
    mapError.value = t('maps.outside_algeria');
    emit('error', mapError.value);
    return;
  }

  mapError.value = '';
  emit('update:latitude', nextLatitude);
  emit('update:longitude', nextLongitude);
  setMarker(nextLatitude, nextLongitude);
  await reverseGeocode(nextLatitude, nextLongitude);
}

async function reverseGeocode(lat, lng) {
  const reverseUrl = config.value?.geocoder?.reverse_url;

  if (!reverseUrl) {
    return;
  }

  try {
    const url = reverseUrl
      .replace('{lat}', encodeURIComponent(String(lat)))
      .replace('{lng}', encodeURIComponent(String(lng)))
      .replace('{locale}', encodeURIComponent(locale.value || 'ar'));

    const response = await fetch(url, {
      headers: {
        Accept: 'application/json',
      },
    });

    if (!response.ok) {
      throw new Error(`Reverse geocoding failed with status ${response.status}`);
    }

    const payload = await response.json();
    const countryCode = (payload?.address?.country_code || payload?.features?.[0]?.properties?.country_code || '').toLowerCase();

    if (countryCode && countryCode !== 'dz') {
      mapError.value = t('maps.outside_algeria');
      emit('error', mapError.value);
      return;
    }

    const label = payload?.display_name || payload?.features?.[0]?.place_name || '';
    const wilaya = resolveWilaya(payload);

    if (label) {
      emit('label-suggested', label);
    }

    if (wilaya) {
      emit('wilaya-suggested', wilaya);
    }
  } catch (error) {
    mapError.value = t('maps.reverse_geocode_failed');
  }
}

function resolveWilaya(payload) {
  const address = payload?.address || {};
  const candidates = [
    address.state,
    address.county,
    address.city,
    address.town,
    address.municipality,
    payload?.features?.[0]?.text,
    payload?.features?.[0]?.context?.find((item) => item.id?.startsWith('region'))?.text,
  ].filter(Boolean);

  return props.wilayaOptions.find((wilaya) => candidates.some((candidate) => normalize(candidate).includes(normalize(wilaya)) || normalize(wilaya).includes(normalize(candidate)))) || '';
}

function normalize(value) {
  return String(value || '')
    .toLowerCase()
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .replace(/[^a-z0-9]/g, '');
}

watch(
  () => [numericLatitude.value, numericLongitude.value],
  ([lat, lng]) => {
    if (lat !== null && lng !== null) {
      setMarker(lat, lng, false);
    }
  },
);

onMounted(initializeMap);

onBeforeUnmount(() => {
  if (map) {
    map.remove();
    map = null;
  }
});
</script>
