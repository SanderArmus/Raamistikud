<script setup lang="ts">
import Input from '@/components/ui/input/Input.vue';
import Label from '@/components/ui/label/Label.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { create, store } from '@/routes/posts';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import Switch from '@/components/ui/switch/Switch.vue';
import Button from '@/components/ui/button/Button.vue';
import Textarea from '@/components/ui/textarea/Textarea.vue';
import InputError from '@/components/InputError.vue';
import { Select, SelectContent, SelectGroup, SelectItem,  SelectTrigger, SelectValue } from '@/components/ui/select';
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Posts create',
        href: create().url,
    },
];

const form = useForm({
    title: '',
    content: '',
    author_id: '',
    published: false,
});

const props = defineProps<{authors: Record<number, string>;}>();
console.log(props.authors);

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
                                <Label for="content">Content</Label>
                                <Textarea class="mt-1 " id="content" v-model="form.content" />
                                <InputError :message="form.errors.content"/>

                            </div>
                            <div>
                                <Label for="author">Author</Label>
                                  <Select v-model="form.author_id">
                                    <SelectTrigger>
                                      <SelectValue placeholder="Select an author" />
                                    </SelectTrigger>
                                    <SelectContent class="w-(--reka-select-trigger-width)">
                                      <SelectGroup>
                                        <SelectItem
                                          v-for="(name, id) in authors"
                                          :key="id"
                                          :value="id"
                                        >
                                          {{ name }}
                                        </SelectItem>
                                      </SelectGroup>
                                    </SelectContent>
                                  </Select>
                                <InputError :message="form.errors.author_id"/>
                            </div>

                            <div class="flex items-center space-x-2">
                                <Switch id="published" />
                                <Label for="published">Published</Label>
                            </div>
                            <div class="flex justify-end mt-6">
                                <Button type="submit">Save</Button>
                            </div>
                        </div>
                    </form>
                <pre>{{ form }}</pre>
            </div>
        </div>
    </AppLayout>
</template>
