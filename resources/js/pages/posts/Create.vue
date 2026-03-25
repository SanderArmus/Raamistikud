<script setup lang="ts">
import Input from '@/components/ui/input/Input.vue';
import Label from '@/components/ui/label/Label.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { create, store } from '@/routes/posts';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import Button from '@/components/ui/button/Button.vue';
import Textarea from '@/components/ui/textarea/Textarea.vue';
import InputError from '@/components/InputError.vue';
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Posts create',
        href: create().url,
    },
];

const form = useForm({
    title: '',
    description: '',
});

const submit = () => {
    form.post(store().url);
};

</script>


<template>
    <Head title="Posts" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <div class="mx-auto h-full w-full max-w-2xl bg-muted p-4">
                <h3 class="text-lg font-medium">Post create</h3>
                    <form @submit.prevent="submit">
                        <div class="grid gap-4 mt-6">
                            <div>
                                <Label for="title">Title</Label>
                                <Input class="mt-1" name="title" v-model="form.title" />
                                <InputError :message="form.errors.title"/>
                            </div>
                            <div>
                                <Label for="description">Description</Label>
                                <Textarea class="mt-1" id="description" v-model="form.description" />
                                <InputError :message="form.errors.description" />
                            </div>
                            <div class="flex justify-end mt-6">
                                <Button type="submit">Save</Button>
                            </div>
                        </div>
                    </form>
                <!-- Debug output removed: form state object was rendering on screen. -->
            </div>
        </div>
    </AppLayout>
</template>
