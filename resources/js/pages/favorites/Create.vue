<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';
import Textarea from '@/components/ui/textarea/Textarea.vue';
import { Label } from '@/components/ui/label';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Favorites', href: '/favorites' },
  { title: 'Add', href: '/favorites/create' },
];

const form = useForm({
  title: '',
  image: '',
  description: '',
  director: '',
  release_year: new Date().getFullYear(),
  genre: '',
});

const submit = () => {
  form.post('/favorites');
};
</script>

<template>
  <Head title="Add favorite" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6 max-w-2xl">
      <div class="rounded-xl border border-sidebar-border/70 bg-background/30 p-4 dark:border-sidebar-border">
        <h1 class="text-2xl font-semibold">Add a movie</h1>

        <form class="mt-4 grid gap-4" @submit.prevent="submit">
          <div>
            <Label for="title">Title</Label>
            <Input id="title" v-model="form.title" />
            <div v-if="form.errors.title" class="mt-1 text-sm text-red-600">{{ form.errors.title }}</div>
          </div>

          <div class="grid gap-4 sm:grid-cols-2">
            <div>
              <Label for="director">Director</Label>
              <Input id="director" v-model="form.director" />
              <div v-if="form.errors.director" class="mt-1 text-sm text-red-600">{{ form.errors.director }}</div>
            </div>
            <div>
              <Label for="release_year">Release year</Label>
              <Input id="release_year" type="number" v-model="form.release_year" />
              <div v-if="form.errors.release_year" class="mt-1 text-sm text-red-600">{{ form.errors.release_year }}</div>
            </div>
          </div>

          <div>
            <Label for="genre">Genre</Label>
            <Input id="genre" v-model="form.genre" placeholder="e.g. Sci-Fi" />
            <div v-if="form.errors.genre" class="mt-1 text-sm text-red-600">{{ form.errors.genre }}</div>
          </div>

          <div>
            <Label for="image">Image URL</Label>
            <Input id="image" v-model="form.image" placeholder="https://..." />
            <div v-if="form.errors.image" class="mt-1 text-sm text-red-600">{{ form.errors.image }}</div>
          </div>

          <div>
            <Label for="description">Description</Label>
            <Textarea id="description" v-model="form.description" rows="6" />
            <div v-if="form.errors.description" class="mt-1 text-sm text-red-600">{{ form.errors.description }}</div>
          </div>

          <div class="flex items-center justify-between gap-3">
            <Button as-child variant="outline">
              <Link href="/favorites">Back</Link>
            </Button>
            <Button type="submit" :disabled="form.processing">
              {{ form.processing ? 'Saving...' : 'Save' }}
            </Button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>

