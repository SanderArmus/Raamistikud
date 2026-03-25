<script setup lang="ts">
import Input from '@/components/ui/input/Input.vue';
import Label from '@/components/ui/label/Label.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { create, store } from '@/routes/authors';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import Button from '@/components/ui/button/Button.vue';
import InputError from '@/components/InputError.vue';
import Calendar from '@/components/ui/calendar/Calendar.vue';
import Popover from '@/components/ui/popover/Popover.vue';
import PopoverContent from '@/components/ui/popover/PopoverContent.vue';
import PopoverTrigger from '@/components/ui/popover/PopoverTrigger.vue';
import { CalendarIcon } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import type { CalendarDate } from '@internationalized/date';
import { cn } from '@/lib/utils';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Authors create',
        href: create().url,
    },
];

const dateOfBirth = ref<CalendarDate | undefined>(undefined);

const formattedDate = computed(() => {
    if (!dateOfBirth.value) return 'Pick a date';
    return new Date(dateOfBirth.value.year, dateOfBirth.value.month - 1, dateOfBirth.value.day)
        .toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
});

const form = useForm({
    first_name: '',
    last_name: '',
    date_of_birth: '',
});

const submit = () => {
    // Convert CalendarDate to string format (YYYY-MM-DD) before submitting
    if (dateOfBirth.value) {
        form.date_of_birth = `${dateOfBirth.value.year}-${String(dateOfBirth.value.month).padStart(2, '0')}-${String(dateOfBirth.value.day).padStart(2, '0')}`;
    }
    
    form.post(store().url);
};
</script>

<template>
    <Head title="Authors" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <div class="mx-auto h-full w-full max-w-2xl bg-muted p-4">
                <h3 class="text-lg font-medium">Author create</h3>
                <form @submit.prevent="submit">
                    <div class="grid gap-4 mt-6">
                        <div>
                            <Label for="first_name">First Name</Label>
                            <Input class="mt-1" name="first_name" v-model="form.first_name" />
                            <InputError :message="form.errors.first_name"/>
                        </div>
                        <div>
                            <Label for="last_name">Last Name</Label>
                            <Input class="mt-1" name="last_name" v-model="form.last_name" />
                            <InputError :message="form.errors.last_name"/>
                        </div>
                        <div>
                            <Label for="date_of_birth">Date of Birth</Label>
                            <Popover>
                                <PopoverTrigger as-child>
                                    <Button
                                        id="date_of_birth"
                                        variant="outline"
                                        :class="cn(
                                            'w-full justify-start text-left font-normal mt-1',
                                            !dateOfBirth && 'text-muted-foreground'
                                        )"
                                        type="button"
                                    >
                                        <CalendarIcon class="mr-2 h-4 w-4" />
                                        {{ formattedDate }}
                                    </Button>
                                </PopoverTrigger>
                                <PopoverContent class="w-auto p-0">
                                    <Calendar :message="dateOfBirth" initial-focus />
                                </PopoverContent>
                            </Popover>
                            <InputError :message="form.errors.date_of_birth"/>
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