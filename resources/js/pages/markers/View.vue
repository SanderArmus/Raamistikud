<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import Input from '@/components/ui/input/Input.vue';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import type { BreadcrumbItem } from '@/types';

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
  marker: Marker;
}>();

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Markers', href: '/markers' },
  { title: props.marker.name, href: `/markers/${props.marker.id}` },
];

const form = useForm({
  name: props.marker.name,
  latitude: props.marker.latitude,
  longitude: props.marker.longitude,
  description: props.marker.description,
});

const updateMarker = () => {
  form.put(`/markers/${props.marker.id}`, {
    onSuccess: () => {
      // Inertia will update the props from the controller's redirect.
    },
  });
};

const deleteMarker = () => {
  if (!confirm('Delete this marker?')) return;
  form.delete(`/markers/${props.marker.id}`, {
    onSuccess: () => {
      // Redirect handled server-side.
    },
  });
};
</script>

<template>
  <Head :title="`Marker #${props.marker.id}`" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="max-w-2xl mx-auto p-4 flex flex-col gap-6">
      <div class="flex items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl font-semibold">Marker</h1>
          <p class="mt-1 text-sm opacity-70">ID: {{ props.marker.id }}</p>
        </div>
        <div class="flex items-center gap-2">
          <Button as-child variant="outline">
            <Link href="/markers">Back to map</Link>
          </Button>
        </div>
      </div>

      <div class="rounded-xl border border-sidebar-border/70 bg-background/30 p-4 dark:border-sidebar-border">
        <div class="grid gap-4 sm:grid-cols-2">
          <div>
            <Label for="name">Name</Label>
            <Input id="name" v-model="form.name" placeholder="Name" />
          </div>
          <div>
            <Label for="coords">Coordinates</Label>
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
              <Input id="lat" v-model="form.latitude" disabled class="w-full sm:w-1/2" />
              <Input id="lng" v-model="form.longitude" disabled class="w-full sm:w-1/2" />
            </div>
            <p class="mt-1 text-xs opacity-70">Set on map click. Lat/Lng are read-only here.</p>
          </div>
        </div>

        <div class="mt-4">
          <Label for="description">Description</Label>
          <Textarea
            id="description"
            v-model="form.description"
            placeholder="Description..."
          />
        </div>

        <div v-if="form.errors?.description" class="mt-2 text-sm text-red-600">
          {{ form.errors.description }}
        </div>

        <div class="mt-5 flex items-center justify-between gap-4">
          <div class="flex items-center gap-2">
            <Button type="button" variant="outline" @click="deleteMarker">
              Delete
            </Button>
            <Button type="button" variant="default" :disabled="form.processing" @click="updateMarker">
              Save changes
            </Button>
          </div>
          <div class="text-xs opacity-70 text-right">
            <div>Added: {{ props.marker.added }}</div>
            <div v-if="props.marker.edited">Edited: {{ props.marker.edited }}</div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

