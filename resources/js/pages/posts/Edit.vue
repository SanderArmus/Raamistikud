<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { index, update } from '@/routes/posts';
import type { BreadcrumbItem } from '@/types';
import InputError from '@/components/InputError.vue';

const props = defineProps<{
    post: {
        id: number;
        title: string;
        description: string;
        created_at_formatted?: string;
        updated_at_formatted?: string;
    };
}>();



const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Posts', href: index().url },
    { title: `Edit Post #${props.post.id}`, href: `/posts/${props.post.id}/edit` },
];

const form = useForm({
    title: props.post.title,
    description: props.post.description,
});
const submit = () => {
    form.put(update(props.post.id).url)
};

</script>

<template>
    <Head :title="`Edit Post #${props.post.id}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="max-w-2xl mx-auto p-6 flex flex-col gap-6">
            <h1 class="text-2xl font-semibold">Edit Post</h1>

            <form @submit.prevent="submit" class="flex flex-col gap-4">
                <div>
                    <Label for="title">Title</Label>
                    <Input id="title" v-model="form.title" />
                    <p v-if="form.errors.title" class="text-red-600 text-sm">
                        {{ form.errors.title }}
                    </p>
                </div>


                <div>
                    <Label for="description">Description</Label>
                    <Textarea id="description" rows="6" v-model="form.description" />
                    <p v-if="form.errors.description" class="text-red-600 text-sm">
                        {{ form.errors.description }}
                    </p>
                </div>

                <div class="text-sm text-gray-500 mt-2">
                    <p>Created at: {{ props.post.created_at_formatted }}</p>
                    <p>Last updated: {{ props.post.updated_at_formatted }}</p>
                </div>

                <div class="flex gap-3 mt-4">
                    <Button type="submit" :disabled="form.processing">
                        Save Changes
                    </Button>
                    <Button
                        type="button"
                        variant="outline"
                        @click="router.visit(index().url)"
                    >
                        Cancel
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
