<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';
import { Label } from '@/components/ui/label';
import type { BreadcrumbItem } from '@/types';

type Product = {
  id: number;
  name: string;
  price: number; // euros (integer)
};

type CheckoutItem = {
  product: Product;
  quantity: number;
  line_total_euros: number;
};

const props = defineProps<{
  items: CheckoutItem[];
  total_euros: number;
  prefill: {
    first_name: string;
    last_name: string;
    email: string;
    phone: string;
  };
}>();

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Shop', href: '/shop' },
  { title: 'Cart', href: '/cart' },
  { title: 'Checkout', href: '/checkout' },
];

const form = useForm({
  first_name: props.prefill.first_name ?? '',
  last_name: props.prefill.last_name ?? '',
  email: props.prefill.email ?? '',
  phone: props.prefill.phone ?? '',
  payment_method: 'stripe' as 'stripe',
});

type FlashProps = {
  flash?: {
    success?: string | null;
    error?: string | null;
  };
};

const submit = () => {
  form.post('/checkout/stripe');
};
</script>

<template>
  <Head title="Checkout" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6 grid gap-6 lg:grid-cols-2">
      <div class="rounded-xl border border-sidebar-border/70 bg-background/30 p-4 dark:border-sidebar-border">
        <h2 class="text-lg font-semibold">Your details</h2>
        <div
          v-if="$page.props && (($page.props as FlashProps).flash?.error || (form as any).errors?.error)"
          class="mt-3 rounded-lg border border-red-300 bg-red-50 px-3 py-2 text-sm text-red-700"
        >
          {{ ($page.props as FlashProps).flash?.error || (form as any).errors?.error }}
        </div>

        <form class="mt-4 grid gap-4" @submit.prevent="submit">
          <div class="grid gap-4 sm:grid-cols-2">
            <div>
              <Label for="first_name">First name</Label>
              <Input id="first_name" v-model="form.first_name" />
              <div v-if="form.errors.first_name" class="mt-1 text-sm text-red-600">{{ form.errors.first_name }}</div>
            </div>
            <div>
              <Label for="last_name">Last name</Label>
              <Input id="last_name" v-model="form.last_name" />
              <div v-if="form.errors.last_name" class="mt-1 text-sm text-red-600">{{ form.errors.last_name }}</div>
            </div>
          </div>

          <div class="grid gap-4 sm:grid-cols-2">
            <div>
              <Label for="email">Email</Label>
              <Input id="email" type="email" v-model="form.email" />
              <div v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</div>
            </div>
            <div>
              <Label for="phone">Phone</Label>
              <Input id="phone" v-model="form.phone" />
              <div v-if="form.errors.phone" class="mt-1 text-sm text-red-600">{{ form.errors.phone }}</div>
            </div>
          </div>

          <div class="rounded-lg border border-sidebar-border/70 bg-background/30 p-3 dark:border-sidebar-border">
            <div class="text-sm font-semibold">Payment method</div>
            <div class="mt-2 flex items-center gap-2 text-sm">
              <input id="pm_stripe" type="radio" value="stripe" v-model="form.payment_method" />
              <Label for="pm_stripe">Stripe Checkout</Label>
            </div>
          </div>

          <div class="flex items-center justify-between gap-3">
            <Button as-child variant="outline">
              <Link href="/cart">Back to cart</Link>
            </Button>
            <Button type="submit" :disabled="form.processing || props.items.length === 0">
              {{ form.processing ? 'Opening Stripe...' : `Pay ${props.total_euros} €` }}
            </Button>
          </div>
        </form>
      </div>

      <div class="rounded-xl border border-sidebar-border/70 bg-background/30 p-4 dark:border-sidebar-border">
        <h2 class="text-lg font-semibold">Order summary</h2>
        <div class="mt-4 grid gap-2">
          <div v-for="item in props.items" :key="item.product.id" class="flex items-center justify-between text-sm">
            <div class="opacity-80">
              {{ item.product.name }} x{{ item.quantity }}
            </div>
            <div class="font-semibold">{{ item.line_total_euros }} €</div>
          </div>
        </div>

        <div class="mt-4 flex justify-end text-sm">
          Total: <span class="ml-2 font-semibold">{{ props.total_euros }} €</span>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

