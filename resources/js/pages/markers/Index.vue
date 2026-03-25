<script setup lang="ts">
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { nextTick, onMounted, ref, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import PlaceholderPattern from '@/components/PlaceholderPattern.vue';
import { Button } from '@/components/ui/button';
import Input from '@/components/ui/input/Input.vue';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import type { BreadcrumbItem } from '@/types';

declare const google: any;

type Marker = {
  id: number;
  name: string;
  latitude: number;
  longitude: number;
  description: string;
  added: string;
  edited: string | null;
};

const props = defineProps<{
  markers: Marker[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Markers', href: '/markers' },
];

const mapEl = ref<HTMLDivElement | null>(null);
const map = ref<any>(null);

const apiKey = (import.meta.env.VITE_GOOGLE_MAPS_API_KEY as string | undefined)?.trim();
const loadError = ref<string | null>(null);
const isLoading = ref(false);

const newMarkerLatLng = ref<{ lat: number; lng: number } | null>(null);
const createForm = useForm({
  name: '',
  description: '',
  latitude: 0,
  longitude: 0,
});

const isMapReady = ref(false);

let googleMapsPromise: Promise<void> | null = null;
const loadGoogleMaps = async (): Promise<void> => {
  if (typeof window === 'undefined') return;
  if ((window as any).google?.maps) return;

  if (!apiKey) {
    throw new Error('Missing VITE_GOOGLE_MAPS_API_KEY.');
  }

  if (googleMapsPromise) return googleMapsPromise;

  googleMapsPromise = new Promise((resolve, reject) => {
    const existing = document.querySelector<HTMLScriptElement>(
      'script[data-google-maps-loader="true"]',
    );
    if (existing) {
      existing.addEventListener('load', () => resolve());
      existing.addEventListener('error', () => reject(new Error('Google Maps script failed.')));
      return;
    }

    const script = document.createElement('script');
    script.dataset.googleMapsLoader = 'true';
    script.src = `https://maps.googleapis.com/maps/api/js?key=${encodeURIComponent(apiKey)}&v=weekly`;
    script.async = true;
    script.defer = true;
    script.onload = () => resolve();
    script.onerror = () => reject(new Error('Google Maps script failed.'));
    document.head.appendChild(script);
  });

  return googleMapsPromise;
};

const markerObjects: any[] = [];
const clearMarkers = () => {
  markerObjects.forEach((m) => m.setMap(null));
  markerObjects.length = 0;
};

const addMarkersToMap = (items: Marker[]) => {
  if (!map.value || !google?.maps) return;

  clearMarkers();
  items.forEach((item) => {
    const gm = new google.maps.Marker({
      position: { lat: Number(item.latitude), lng: Number(item.longitude) },
      map: map.value,
      title: item.name,
    });

    gm.addListener('click', () => {
      router.visit(`/markers/${item.id}`);
    });

    markerObjects.push(gm);
  });
};

const openCreate = (lat: number, lng: number) => {
  newMarkerLatLng.value = { lat, lng };
  createForm.name = '';
  createForm.description = '';
  createForm.latitude = lat;
  createForm.longitude = lng;
};

const closeCreate = () => {
  newMarkerLatLng.value = null;
  createForm.name = '';
  createForm.description = '';
  createForm.latitude = 0;
  createForm.longitude = 0;
};

const createMarker = () => {
  createForm.post('/markers', {
    onSuccess: () => {
      closeCreate();
      router.visit('/markers');
    },
  });
};

onMounted(async () => {
  isLoading.value = true;
  try {
    await loadGoogleMaps();
    // Wait for the map container to be rendered (it is behind the isLoading v-else-if).
    isLoading.value = false;
    await nextTick();
    if (!mapEl.value) return;

    const initial =
      props.markers.length > 0
        ? { lat: Number(props.markers[0].latitude), lng: Number(props.markers[0].longitude) }
        : { lat: 59.437, lng: 24.7536 };

    map.value = new google.maps.Map(mapEl.value, {
      center: initial,
      zoom: 11,
    });

    map.value.addListener('click', (e: any) => {
      const lat = e.latLng.lat();
      const lng = e.latLng.lng();
      openCreate(lat, lng);
    });

    addMarkersToMap(props.markers);
    isMapReady.value = true;
  } catch (e: any) {
    loadError.value = e?.message ?? 'Failed to load Google Maps.';
  }
});

watch(
  () => props.markers,
  (items) => {
    if (!map.value) return;
    addMarkersToMap(items);
  },
  { deep: true },
);
</script>

<template>
  <Head title="Markers" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex flex-col gap-4">
      <div class="relative w-full overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
        <div v-if="loadError" class="p-4">
          <p class="text-sm font-semibold">Google Maps error</p>
          <p class="mt-2 text-sm opacity-80">{{ loadError }}</p>
        </div>
        <div v-else-if="isLoading" class="p-4">
          <PlaceholderPattern />
          <p class="mt-2 text-sm opacity-80">Loading map...</p>
        </div>
        <div v-else ref="mapEl" class="h-[60vh] w-full" />

        <!-- Create marker modal -->
        <div
          v-if="newMarkerLatLng"
          class="absolute inset-0 flex items-center justify-center bg-black/40 p-4"
        >
          <div class="w-full max-w-md rounded-xl border border-sidebar-border/70 bg-background p-4 dark:border-sidebar-border">
            <div class="flex items-start justify-between gap-4">
              <div>
                <p class="text-sm font-semibold opacity-90">New marker</p>
                <p class="mt-1 text-xs opacity-70">
                  {{ newMarkerLatLng.lat.toFixed(5) }}, {{ newMarkerLatLng.lng.toFixed(5) }}
                </p>
              </div>
              <Button size="sm" variant="ghost" @click="closeCreate">Cancel</Button>
            </div>

            <form class="mt-4 flex flex-col gap-3" @submit.prevent="createMarker">
              <div>
                <Label for="marker-name">Name</Label>
                <Input id="marker-name" v-model="createForm.name" placeholder="e.g. Home" />
              </div>

              <div>
                <Label for="marker-description">Description</Label>
                <Textarea id="marker-description" v-model="createForm.description" placeholder="What is this place?" />
              </div>

              <div class="flex justify-end gap-2">
                <Button type="button" variant="outline" @click="closeCreate">
                  Close
                </Button>
                <Button type="submit" :disabled="createForm.processing || !createForm.name">
                  Save marker
                </Button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <div class="rounded-xl border border-sidebar-border/70 bg-background/30 p-4 dark:border-sidebar-border">
        <div class="flex items-center justify-between gap-3">
          <p class="text-sm font-semibold opacity-90">Markers:</p>
            <p class="text-xs opacity-70">{{ props.markers.length }} total</p>
        </div>
        <div class="mt-3 grid gap-2 md:grid-cols-2">
          <Link
              v-for="m in props.markers"
            :key="m.id"
            :href="`/markers/${m.id}`"
            class="rounded-lg border border-sidebar-border/70 bg-background/30 p-3 transition hover:bg-background/50 dark:border-sidebar-border"
          >
            <div class="text-sm font-semibold">{{ m.name }}</div>
            <div class="mt-1 text-xs opacity-70">
              {{ Number(m.latitude).toFixed(3) }}, {{ Number(m.longitude).toFixed(3) }}
            </div>
          </Link>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

