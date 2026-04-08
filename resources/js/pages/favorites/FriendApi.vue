<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import Button from '@/components/ui/button/Button.vue';
import type { BreadcrumbItem } from '@/types';

type FriendMovie = {
  id: number;
  title: string;
  image?: string | null;
  description?: string | null;
  director?: string | null;
  release_year?: number | null;
  genre?: string | null;
  rating?: number | null;
  user_id?: number | null;
  created_at?: string | null;
  updated_at?: string | null;
};

const props = defineProps<{
  source_url: string;
  cached_ttl_seconds: number;
  payload: {
    success?: boolean;
    error?: string;
    count?: number;
    data?: FriendMovie[];
  };
}>();

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'API', href: '/favorites' },
  { title: 'Kevini filmid', href: '/favorites/friend-api' },
];
</script>

<template>
  <Head title="Kevini filmid" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6 flex flex-col gap-4">
      <div class="flex items-center justify-between gap-3">
        <div>
          <h1 class="text-2xl font-semibold">Kevini filmid</h1>
          <div class="mt-1 text-sm opacity-80">
            Source: <code>{{ props.source_url }}</code> • cached {{ props.cached_ttl_seconds }}s
          </div>
        </div>
        <div class="flex items-center gap-2">
          <Button as-child variant="outline">
            <Link href="/favorites">Back</Link>
          </Button>
          <Button as-child>
            <a :href="props.source_url" target="_blank" rel="noopener noreferrer">Open JSON</a>
          </Button>
        </div>
      </div>

      <div v-if="props.payload?.success === false" class="rounded-xl border border-red-300 bg-red-50 p-4 text-sm text-red-700">
        {{ props.payload.error || 'Friend API failed.' }}
      </div>

      <div class="rounded-xl border border-sidebar-border/70 bg-background/30 p-4 dark:border-sidebar-border">
        <div class="text-sm opacity-80">
          Count: <span class="font-semibold">{{ props.payload?.count ?? props.payload?.data?.length ?? 0 }}</span>
        </div>
      </div>

      <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div
          v-for="m in props.payload.data || []"
          :key="m.id"
          class="rounded-xl border border-sidebar-border/70 bg-background/30 p-4 dark:border-sidebar-border"
        >
          <div class="flex gap-3">
            <div class="h-20 w-20 shrink-0 overflow-hidden rounded-lg bg-muted/40">
              <img v-if="m.image" :src="m.image" :alt="m.title" class="h-full w-full object-cover" />
            </div>
            <div class="min-w-0">
              <div class="font-semibold truncate">{{ m.title }}</div>
              <div class="text-xs opacity-70">
                <span v-if="m.director">{{ m.director }}</span>
                <span v-if="m.release_year"> • {{ m.release_year }}</span>
                <span v-if="m.genre"> • {{ m.genre }}</span>
                <span v-if="m.rating != null"> • rating {{ m.rating }}</span>
              </div>
              <div v-if="m.description" class="mt-2 text-sm opacity-80 line-clamp-3">{{ m.description }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

