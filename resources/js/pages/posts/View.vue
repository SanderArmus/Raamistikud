<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import Button from '@/components/ui/button/Button.vue';
import Textarea from '@/components/ui/textarea/Textarea.vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { edit, index, show } from '@/routes/posts';
import type { BreadcrumbItem } from '@/types';

type PostComment = {
  id: number;
  content: string;
  user: {
    id: number;
    name: string;
  } | null;
  created_at_formatted: string;
};

type PostPayload = {
  id: number;
  title: string;
  description: string;
  created_at: string;
  updated_at: string;
  created_at_formatted: string;
  updated_at_formatted: string;
  comments: PostComment[];
};

const props = defineProps<{
  post: PostPayload;
}>();

const form = useForm({
  content: '',
});

const submit = () => {
  form.post(`/posts/${props.post.id}/comments`, {
    preserveScroll: true,
    onSuccess: () => form.reset(),
  });
};

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Posts', href: index().url },
  { title: props.post.title, href: show.url(props.post.id) },
];

const commentAuthorInitials = (name?: string | null) => {
  if (!name) return '?';
  return name
    .split(' ')
    .filter(Boolean)
    .map((segment) => segment[0]?.toUpperCase())
    .join('')
    .slice(0, 2);
};

const page = usePage();
const isAdmin = computed(() => Boolean((page.props.auth?.user as any)?.is_admin));
</script>

<template>
  <Head :title="props.post.title" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-col gap-6 overflow-x-auto rounded-xl p-6">
      <div class="flex flex-col gap-4 rounded-xl border border-border/60 bg-muted/40 p-6 shadow-sm">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
          <div>
            <h1 class="text-3xl font-semibold tracking-tight">{{ props.post.title }}</h1>
            <p class="text-sm text-muted-foreground">Post</p>
          </div>

          <div class="flex flex-wrap items-center gap-3">
            <Button as-child variant="outline">
              <Link :href="edit.url(props.post.id)">Edit Post</Link>
            </Button>

            <Button as-child variant="ghost">
              <Link :href="index().url">Back to Posts</Link>
            </Button>
          </div>
        </div>

        <dl class="grid gap-4 rounded-lg border border-border/40 bg-background p-4 sm:grid-cols-3">
          <div>
            <dt class="text-xs uppercase tracking-wide text-muted-foreground">Created</dt>
            <dd class="text-sm text-foreground">
              <div>{{ props.post.created_at_formatted }}</div>
              <div class="text-xs text-muted-foreground">{{ props.post.created_at }}</div>
            </dd>
          </div>

          <div>
            <dt class="text-xs uppercase tracking-wide text-muted-foreground">Last updated</dt>
            <dd class="text-sm text-foreground">
              <div>{{ props.post.updated_at_formatted }}</div>
              <div class="text-xs text-muted-foreground">{{ props.post.updated_at }}</div>
            </dd>
          </div>

          <div>
            <dt class="text-xs uppercase tracking-wide text-muted-foreground">Post ID</dt>
            <dd class="text-sm text-foreground">#{{ props.post.id }}</dd>
          </div>
        </dl>
      </div>

      <section class="rounded-xl border border-border/60 bg-background p-6 shadow-sm">
        <h2 class="mb-4 text-lg font-semibold text-foreground">Content</h2>
        <div class="prose max-w-none text-sm leading-relaxed text-foreground/90 dark:prose-invert">
          <p class="whitespace-pre-line">{{ props.post.description }}</p>
        </div>
      </section>

      <section class="rounded-xl border border-border/60 bg-background p-6 shadow-sm">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
          <h2 class="text-lg font-semibold text-foreground">Comments</h2>
          <span class="text-sm text-muted-foreground">
            {{ props.post.comments.length }} {{ props.post.comments.length === 1 ? 'comment' : 'comments' }}
          </span>
        </div>

        <form class="mb-6 flex flex-col gap-3 sm:flex-row" @submit.prevent="submit">
          <Textarea v-model="form.content" class="flex-1" placeholder="Add a comment" />
          <Button type="submit" :disabled="form.processing">Add Comment</Button>
        </form>

        <ul v-if="props.post.comments.length" class="space-y-4">
          <li
            v-for="comment in props.post.comments"
            :key="comment.id"
            class="rounded-xl border border-border/40 bg-muted/30 p-4"
          >
            <div class="mb-3 flex flex-wrap items-start justify-between gap-3">
              <div class="flex items-center gap-3">
                <div
                  class="flex h-10 w-10 items-center justify-center rounded-full bg-muted text-sm font-semibold text-muted-foreground"
                >
                  {{ commentAuthorInitials(comment.user?.name) }}
                </div>
                <div>
                  <p class="text-sm font-medium text-foreground">
                    {{ comment.user?.name ?? 'Anonymous' }}
                  </p>
                  <p class="text-xs text-muted-foreground">{{ comment.created_at_formatted }}</p>
                </div>
              </div>
              <span class="text-xs text-muted-foreground">#{{ comment.id }}</span>
            </div>

            <div v-if="isAdmin" class="mb-2 flex justify-end">
              <Button
                type="button"
                variant="ghost"
                class="px-2"
                @click="router.delete(`/comments/${comment.id}`, { preserveScroll: true })"
              >
                Delete
              </Button>
            </div>

            <p class="whitespace-pre-line text-sm leading-relaxed text-foreground/90">
              {{ comment.content }}
            </p>
          </li>
        </ul>
        <div v-else class="text-sm opacity-70">
          No comments yet. Be the first to add one.
        </div>
      </section>
    </div>
  </AppLayout>
</template>

