<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import Pagination from '@/components/ui/pagination/Pagination.vue';
import PaginationContent from '@/components/ui/pagination/PaginationContent.vue';
import PaginationEllipsis from '@/components/ui/pagination/PaginationEllipsis.vue';
import PaginationFirst from '@/components/ui/pagination/PaginationFirst.vue';
import PaginationItem from '@/components/ui/pagination/PaginationItem.vue';
import PaginationLast from '@/components/ui/pagination/PaginationLast.vue';
import PaginationNext from '@/components/ui/pagination/PaginationNext.vue';
import PaginationPrevious from '@/components/ui/pagination/PaginationPrevious.vue';
import { Table, TableBody, TableCaption, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { create, destroy, edit, show, index } from '@/routes/posts';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { MoreVertical } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

// Breadcrumbs for layout navigation
const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Posts',
    href: index().url,
  },
];

// Type definitions
interface PaginationLink {
  url: string | null;
  label: string;
  page?: number | null;
  active: boolean;
}

interface PaginatedResponse {
  current_page: number;
  data: Post[];
  first_page_url: string;
  from: number;
  last_page: number;
  last_page_url: string;
  links: PaginationLink[];
  next_page_url: string | null;
  path: string;
  per_page: number;
  prev_page_url: string | null;
  to: number;
  total: number;
}

export type Comment = {
  id: number;
  post_id: number;
  content: string;
  user_id: number;
  created_at_formatted: string;
  updated_at_formatted: string;
  user: User;
};

type User = {
  id: number;
  name: string;
  email: string;
};

export type Post = {
  id: number;
  title: string;
  created_at: string;
  updated_at: string;
  created_at_formatted: string;
  updated_at_formatted: string;
  can_edit: boolean;
};


const props = defineProps<{
  posts: PaginatedResponse;
}>();

const posts = computed(() => props.posts);
const page = usePage();
const isAdmin = computed(() => Boolean((page.props.auth?.user as any)?.is_admin));

const selectedPostIds = ref<number[]>([]);

const pagePostIds = computed(() => posts.value.data.map((p) => p.id));

const allSelectedOnPage = computed(() => {
  if (pagePostIds.value.length === 0) return false;
  return pagePostIds.value.every((id) => selectedPostIds.value.includes(id));
});

const isSelected = (id: number) => selectedPostIds.value.includes(id);

const togglePostSelection = (id: number) => {
  if (isSelected(id)) {
    selectedPostIds.value = selectedPostIds.value.filter((x) => x !== id);
  } else {
    selectedPostIds.value = Array.from(new Set([...selectedPostIds.value, id]));
  }
};

const toggleSelectAllOnPage = () => {
  if (allSelectedOnPage.value) {
    selectedPostIds.value = selectedPostIds.value.filter((id) => !pagePostIds.value.includes(id));
    return;
  }

  selectedPostIds.value = Array.from(new Set([...selectedPostIds.value, ...pagePostIds.value]));
};

watch(
  () => posts.value.current_page,
  () => {
    selectedPostIds.value = [];
  },
);

const deleteSelected = () => {
  if (selectedPostIds.value.length === 0) return;
  if (!confirm(`Delete ${selectedPostIds.value.length} selected post(s)?`)) return;

  router.post(
    '/posts/bulk-delete',
    { ids: selectedPostIds.value },
    {
      preserveScroll: true,
      onSuccess: () => {
        selectedPostIds.value = [];
      },
    },
  );
};

const deleteAllPosts = () => {
  if (!confirm('Delete ALL posts? This will also delete all associated comments.')) return;

  router.post('/posts/delete-all', {}, { preserveScroll: true });
};
const deletePost = (postId: number) => {
  if (!confirm('Aga miks sa kustutad?')) return;
  router.delete(destroy.url(postId), {
    preserveScroll: true,
    onSuccess: () => {
      console.log('Postitus sai kustutatud.');
    },
    onError: (err) => {
      console.error(err);
      alert('Ups, sa ei saanud eluga hakkama.');
    },
  });
}

</script>

<template>

  <Head title="Posts" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-col gap-4 overflow-x-auto rounded-xl p-4">
      <!-- <pre>{{ posts }}</pre> -->
      <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2">
          <Button
            v-if="isAdmin"
            type="button"
            variant="destructive"
            :disabled="selectedPostIds.length === 0"
            @click="deleteSelected"
          >
            Delete selected
          </Button>
          <Button
            v-if="isAdmin"
            type="button"
            variant="outline"
            :disabled="posts.total === 0"
            @click="deleteAllPosts"
          >
            Delete all
          </Button>
        </div>
        <Button as-child variant="default">
          <Link :href="create().url">Add Post</Link>
        </Button>
      </div>

      <Table>
        <TableCaption>A list of your recent blog posts.</TableCaption>
        <TableHeader>
          <TableRow>
            <TableHead class="w-[40px]">
              <input
                type="checkbox"
                class="h-4 w-4 rounded border border-border/60"
                :checked="allSelectedOnPage"
                @change="toggleSelectAllOnPage"
              />
            </TableHead>
            <TableHead>Title</TableHead>
            <TableHead class="text-right">Created at</TableHead>
            <TableHead class="text-right">Updated At</TableHead>
            <TableHead>
              <span class="sr-only">Actions</span>
            </TableHead>
          </TableRow>
        </TableHeader>

        <TableBody>
          <TableRow v-for="post in posts.data" :key="post.id">
            <TableCell class="w-[40px]">
              <input
                type="checkbox"
                class="h-4 w-4 rounded border border-border/60"
                :checked="isSelected(post.id)"
                @change="togglePostSelection(post.id)"
              />
            </TableCell>
            <TableCell>
              <Link :href="show.url(post.id)" class="text-foreground hover:underline">
                {{ post.title }}
              </Link>
            </TableCell>
            <TableCell class="text-right">{{ post.created_at_formatted }}</TableCell>
            <TableCell class="text-right">{{ post.updated_at_formatted }}</TableCell>

            <TableCell>
              <div class="flex justify-end">
                <DropdownMenu>
                  <DropdownMenuTrigger as-child>
                    <Button size="icon" variant="ghost">
                      <MoreVertical />
                    </Button>
                  </DropdownMenuTrigger>

                  <DropdownMenuContent>
                    <DropdownMenuItem as-child>
                      <Link :href="show.url(post.id)">View</Link>
                    </DropdownMenuItem>
                    <DropdownMenuItem
                      v-if="post.can_edit || isAdmin"
                      as-child
                    >
                      <Link :href="edit.url(post.id)">Edit</Link>
                    </DropdownMenuItem>
                    <DropdownMenuSeparator />
                    <DropdownMenuItem
                      v-if="isAdmin"
                      class="text-destructive"
                      @click="deletePost(post.id)"
                    >
                      Delete
                    </DropdownMenuItem>
                  </DropdownMenuContent>
                </DropdownMenu>
              </div>
            </TableCell>
          </TableRow>
        </TableBody>
      </Table>

      <Pagination class="w-full" :page="posts.current_page" v-slot="{ page }" :total="posts.total"
        :items-per-page="posts.per_page" @update:page="(page) => router.get(index().url, { page: page })">
        <PaginationContent v-slot="{ items }" class="flex items-center gap-1">
          <PaginationFirst />
          <PaginationPrevious />

          <template v-for="(item, index) in items" :key="index">
            <PaginationItem v-if="item.type === 'page'" :value="item.value" as-child>
              <Button class="w-10 h-10 p-0" :variant="item.value === page ? 'default' : 'outline'">
                {{ item.value }}
              </Button>
            </PaginationItem>

            <PaginationEllipsis v-else :key="item.type" :index="index" />
          </template>

          <PaginationNext />
          <PaginationLast />
        </PaginationContent>
      </Pagination>
    </div>
  </AppLayout>
</template>
