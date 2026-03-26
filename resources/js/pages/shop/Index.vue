<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';
import type { BreadcrumbItem } from '@/types';
import { ref } from 'vue';

type Product = {
  id: number;
  name: string;
  description: string;
  image_url: string | null;
  price: number; // euros (integer)
  stock_quantity: number;
};

const props = defineProps<{
  products: Product[];
}>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Shop', href: '/shop' }];

const qtyByProductId = ref<Record<number, number>>({});
props.products.forEach((p) => {
  qtyByProductId.value[p.id] = 1;
});

const addToCart = (product: Product) => {
  const raw = Number(qtyByProductId.value[product.id] ?? 1);
  const quantity = Math.max(1, Math.min(raw, Math.max(1, Number(product.stock_quantity))));

  router.post('/cart/add', {
    product_id: product.id,
    quantity,
  }, {
    preserveScroll: true,
  });
};
</script>

<template>
  <Head title="Shop" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex flex-col gap-4 p-6">
      <div class="flex items-center justify-between gap-3">
        <h1 class="text-2xl font-semibold">Shop</h1>
        <Button as-child variant="outline">
          <Link href="/cart">Cart</Link>
        </Button>
      </div>

      <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div
          v-for="p in props.products"
          :key="p.id"
          class="overflow-hidden rounded-xl border border-sidebar-border/70 bg-background/30 dark:border-sidebar-border"
        >
          <div class="aspect-3/2 w-full bg-muted/40">
            <img
              v-if="p.image_url"
              :src="p.image_url"
              :alt="p.name"
              class="h-full w-full object-cover"
              loading="lazy"
            />
          </div>

          <div class="p-4">
            <div class="flex items-start justify-between gap-3">
              <div>
                <div class="text-sm font-semibold">{{ p.name }}</div>
                <div class="mt-1 text-xs opacity-70">{{ p.description }}</div>
              </div>
              <div class="text-sm font-semibold">{{ p.price }} €</div>
            </div>

            <div class="mt-4 flex items-center justify-between gap-3">
              <div class="flex items-center gap-2">
                <Input
                  type="number"
                  min="1"
                  :max="Math.max(1, p.stock_quantity)"
                  class="w-20"
                  v-model.number="qtyByProductId[p.id]"
                />
                <div class="text-xs opacity-70">
                  Stock: {{ p.stock_quantity }}
                </div>
              </div>
              <Button :disabled="p.stock_quantity <= 0" @click="addToCart(p)">
                Add
              </Button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

