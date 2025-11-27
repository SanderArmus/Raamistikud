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
import { destroy, edit, show, index } from '@/routes/posts';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { MoreVertical } from 'lucide-vue-next';

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
  content: string;
  author_id: number;
  published: boolean;
  created_at: string;
  updated_at: string;
  created_at_formatted: string;
  updated_at_formatted: string;
  author: {
    id: number;
    first_name: string;
    last_name: string;
  };
  comments?:Comment[];
};


defineProps<{
  posts: PaginatedResponse;
}>();
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

      <Table>
        <TableCaption>A list of your recent blog posts.</TableCaption>
        <TableHeader>
          <TableRow>
            <TableHead class="w-[100px]">ID</TableHead>
            <TableHead>Title</TableHead>
            <TableHead>Author</TableHead>
            <TableHead class="text-right">Created at</TableHead>
            <TableHead class="text-right">Updated At</TableHead>
            <TableHead class="text-right">Published</TableHead>
            <TableHead>
              <span class="sr-only">Actions</span>
            </TableHead>
          </TableRow>
        </TableHeader>

        <TableBody>
          <TableRow v-for="post in posts.data" :key="post.id">
            <TableCell class="font-medium">{{ post.id }}</TableCell>
            <TableCell>{{ post.title }}</TableCell>
            <TableCell>{{ post.author.first_name }} {{ post.author.last_name }}</TableCell>
            <TableCell class="text-right">{{ post.created_at_formatted }}</TableCell>
            <TableCell class="text-right">{{ post.updated_at_formatted }}</TableCell>
            <TableCell class="text-right">
              <span :class="post.published ? 'text-green-600' : 'text-gray-400'">
                {{ post.published ? 'Yes' : 'No' }}
              </span>
            </TableCell>

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
                    <DropdownMenuItem as-child>
                      <Link :href="edit.url(post.id)">Edit</Link>
                    </DropdownMenuItem>
                    <DropdownMenuSeparator />
                    <DropdownMenuItem class="text-destructive" @click="deletePost(post.id)">
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
