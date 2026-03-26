<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import Button from '@/components/ui/button/Button.vue';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
  status: 'pending' | 'succeeded' | 'failed' | string;
  message: string;
}>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Checkout', href: '/checkout' }];
</script>

<template>
  <Head title="Payment result" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6 max-w-2xl mx-auto">
      <div class="rounded-xl border border-sidebar-border/70 bg-background/30 p-6 dark:border-sidebar-border">
        <h1 class="text-2xl font-semibold">Payment status: {{ props.status }}</h1>
        <p class="mt-2 text-sm opacity-80">{{ props.message }}</p>

        <div class="mt-6 flex items-center gap-2">
          <Button as-child variant="outline">
            <Link href="/shop">Back to shop</Link>
          </Button>
          <Button as-child>
            <Link href="/cart">Cart</Link>
          </Button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

