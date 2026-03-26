<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';
import type { BreadcrumbItem } from '@/types';

type Product = {
  id: number;
  name: string;
  image_url: string | null;
  price: number; // euros (integer)
  stock_quantity: number;
};

type CartItem = {
  product: Product;
  quantity: number;
  line_total_euros: number;
};

const props = defineProps<{
  items: CartItem[];
  total_euros: number;
}>();

const page = usePage();

type FlashProps = {
  flash?: {
    success?: string | null;
    error?: string | null;
  };
};

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Shop', href: '/shop' },
  { title: 'Cart', href: '/cart' },
];

const updateQty = (productId: number, quantity: number) => {
  router.post('/cart/update', { product_id: productId, quantity }, { preserveScroll: true });
};

const removeItem = (productId: number) => {
  router.delete(`/cart/remove/${productId}`, { preserveScroll: true });
};
</script>

<template>
  <Head title="Cart" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6 flex flex-col gap-4">
      <div class="flex items-center justify-between gap-3">
        <h1 class="text-2xl font-semibold">Cart</h1>
        <div class="flex items-center gap-2">
          <Button as-child variant="outline">
            <Link href="/shop">Back to shop</Link>
          </Button>
          <Button as-child :disabled="props.items.length === 0">
            <Link href="/checkout">Checkout</Link>
          </Button>
        </div>
      </div>

      <div
        v-if="(page.props as FlashProps).flash?.success"
        class="rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-800"
      >
        {{ (page.props as FlashProps).flash?.success }}
      </div>

      <div v-if="props.items.length === 0" class="rounded-xl border border-sidebar-border/70 p-4 opacity-80 dark:border-sidebar-border">
        Cart is empty.
      </div>

      <div v-else class="flex flex-col gap-3">
        <div
          v-for="item in props.items"
          :key="item.product.id"
          class="flex flex-col gap-3 rounded-xl border border-sidebar-border/70 bg-background/30 p-4 dark:border-sidebar-border sm:flex-row sm:items-center sm:justify-between"
        >
          <div class="flex items-center gap-3">
            <div class="h-16 w-24 overflow-hidden rounded-lg bg-muted/40">
              <img v-if="item.product.image_url" :src="item.product.image_url" :alt="item.product.name" class="h-full w-full object-cover" />
            </div>
            <div>
              <div class="text-sm font-semibold">{{ item.product.name }}</div>
              <div class="text-xs opacity-70">{{ item.product.price }} €</div>
            </div>
          </div>

          <div class="flex items-center justify-between gap-3 sm:justify-end">
            <div class="flex items-center gap-2">
              <Input
                type="number"
                min="0"
                :max="Math.max(0, item.product.stock_quantity)"
                class="w-20"
                :model-value="item.quantity"
                @update:model-value="(v:any) => updateQty(item.product.id, Number(v))"
              />
              <div class="text-xs opacity-70">
                Line: {{ item.line_total_euros }} €
              </div>
            </div>
            <Button variant="outline" @click="removeItem(item.product.id)">Remove</Button>
          </div>
        </div>

        <div class="flex justify-end">
          <div class="rounded-xl border border-sidebar-border/70 bg-background/30 p-4 text-sm dark:border-sidebar-border">
            Total: <span class="font-semibold">{{ props.total_euros }} €</span>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

