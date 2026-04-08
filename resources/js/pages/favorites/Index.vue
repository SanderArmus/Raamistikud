<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';
import { Label } from '@/components/ui/label';
import type { BreadcrumbItem } from '@/types';

type Subject = {
  id: number;
  title: string;
  image: string | null;
  description: string;
  director: string;
  release_year: number;
  genre: string | null;
  created_at: string;
  updated_at: string;
};

type Paginated<T> = {
  data: T[];
  links: Array<{ url: string | null; label: string; active: boolean }>;
  meta?: any;
};

const props = defineProps<{
  subjects: Paginated<Subject>;
  filters: {
    search: string;
    director: string;
    genre: string;
    year_from: number | null;
    year_to: number | null;
    sort: 'created_at' | 'title' | 'release_year';
    direction: 'asc' | 'desc';
  };
}>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'API', href: '/favorites' }];

const apply = () => {
  router.get(
    '/favorites',
    {
      ...props.filters,
    },
    { preserveScroll: true, preserveState: true }
  );
};

const page = usePage();
type FlashProps = { flash?: { success?: string | null; error?: string | null } };
</script>

<template>
  <Head title="API" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6 flex flex-col gap-4">
      <div class="flex items-center justify-between gap-3">
        <h1 class="text-2xl font-semibold">API (Movies)</h1>
        <Button as-child>
          <Link href="/favorites/create">Add new</Link>
        </Button>
      </div>

      <div v-if="(page.props as FlashProps).flash?.success" class="rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-800">
        {{ (page.props as FlashProps).flash?.success }}
      </div>

      <div class="rounded-xl border border-sidebar-border/70 bg-background/30 p-4 dark:border-sidebar-border">
        <div class="grid gap-4 md:grid-cols-6">
          <div class="md:col-span-2">
            <Label for="search">Search (title)</Label>
            <Input id="search" v-model="props.filters.search" @keyup.enter="apply" placeholder="e.g. Matrix" />
          </div>
          <div>
            <Label for="director">Director</Label>
            <Input id="director" v-model="props.filters.director" @keyup.enter="apply" placeholder="e.g. Nolan" />
          </div>
          <div>
            <Label for="genre">Genre</Label>
            <Input id="genre" v-model="props.filters.genre" @keyup.enter="apply" placeholder="e.g. Sci-Fi" />
          </div>
          <div>
            <Label for="year_from">Year from</Label>
            <Input id="year_from" type="number" v-model="props.filters.year_from" @keyup.enter="apply" />
          </div>
          <div>
            <Label for="year_to">Year to</Label>
            <Input id="year_to" type="number" v-model="props.filters.year_to" @keyup.enter="apply" />
          </div>
        </div>

        <div class="mt-4 flex flex-wrap items-center gap-2">
          <Button variant="outline" @click="apply">Apply</Button>
          <Button
            variant="outline"
            @click="
              router.get('/favorites', { search: '', director: '', genre: '', year_from: null, year_to: null, sort: 'created_at', direction: 'desc' })
            "
          >
            Clear
          </Button>

          <div class="ml-auto flex items-center gap-2 text-sm">
            <span class="opacity-70">Sort</span>
            <select v-model="props.filters.sort" class="rounded-md border border-sidebar-border/70 bg-background/30 px-2 py-1 text-sm">
              <option value="created_at">Created</option>
              <option value="title">Title</option>
              <option value="release_year">Release year</option>
            </select>
            <select v-model="props.filters.direction" class="rounded-md border border-sidebar-border/70 bg-background/30 px-2 py-1 text-sm">
              <option value="desc">Desc</option>
              <option value="asc">Asc</option>
            </select>
            <Button size="sm" variant="outline" @click="apply">Sort</Button>
          </div>
        </div>
      </div>

      <div v-if="props.subjects.data.length === 0" class="rounded-xl border border-sidebar-border/70 p-4 opacity-80 dark:border-sidebar-border">
        No entries yet.
      </div>

      <div v-else class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div v-for="s in props.subjects.data" :key="s.id" class="rounded-xl border border-sidebar-border/70 bg-background/30 p-4 dark:border-sidebar-border">
          <div class="flex gap-3">
            <div class="h-20 w-20 shrink-0 overflow-hidden rounded-lg bg-muted/40">
              <img v-if="s.image" :src="s.image" :alt="s.title" class="h-full w-full object-cover" />
            </div>
            <div class="min-w-0">
              <div class="font-semibold truncate">{{ s.title }}</div>
              <div class="text-xs opacity-70">{{ s.director }} • {{ s.release_year }} <span v-if="s.genre">• {{ s.genre }}</span></div>
              <div class="mt-2 text-sm opacity-80 line-clamp-3">{{ s.description }}</div>
            </div>
          </div>
        </div>
      </div>

      <div class="flex flex-wrap items-center justify-center gap-2 text-sm">
        <template v-for="link in props.subjects.links" :key="link.label">
          <Link
            v-if="link.url"
            :href="link.url"
            class="rounded-md border border-sidebar-border/70 px-2 py-1"
            :class="link.active ? 'bg-background/60 font-semibold' : 'opacity-80 hover:opacity-100'"
            v-html="link.label"
          />
          <span v-else class="px-2 py-1 opacity-50" v-html="link.label" />
        </template>
      </div>

      <div class="rounded-xl border border-sidebar-border/70 bg-background/30 p-4 text-sm dark:border-sidebar-border">
        JSON API: <code>/api/my-favorite-subjects</code>
        <div class="mt-1 opacity-80">
          Example:
          <code>/api/my-favorite-subjects?search=matrix&amp;sort=release_year&amp;direction=asc&amp;limit=10</code>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

