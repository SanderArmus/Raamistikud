<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { Head, router, useForm } from '@inertiajs/vue3';
import { WeatherData, type BreadcrumbItem } from '@/types';
import { nextTick, onMounted, ref, toRefs, watch } from 'vue';
import { Button } from '@/components/ui/button';
import Input from '@/components/ui/input/Input.vue';
import PlaceholderPattern from '../components/PlaceholderPattern.vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Weather',
        href: dashboard().url,
    },
];
const props = defineProps<{
    weather?: WeatherData | null;
}>();
const { weather } = toRefs(props);

const location = ref('');

const searchWeather = () => {
    const raw = location.value.trim();

    let cityValue = '';
    let countryValue = '';
    if (raw.includes(',')) {
        const parts = raw.split(',');
        cityValue = (parts[0] ?? '').trim();
        countryValue = parts.slice(1).join(',').trim();
    } else {
        cityValue = raw;
    }

    // Empty search -> backend defaults to Tallinn.
    router.get(
        dashboard().url,
        {
            city: cityValue || undefined,
            country: countryValue || undefined,
        },
        { preserveScroll: true },
    );
};

type PickedLatLng = { lat: number; lng: number };
// NOTE: Embedded Google Maps marker picking was removed from Dashboard.
</script>

<template>
    <Head title="Weather" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <!-- <pre>{{ weather }}</pre> -->
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <div class="grid auto-rows-min gap-4 md:grid-cols-1">
                <div
                    v-if="weather?.main?.temp != null && weather?.weather?.length"
                    class="relative h-36 overflow-hidden rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border"
                >
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h2 class="text-lg font-bold leading-tight">{{ weather.main.temp }} °C</h2>
                            <p class="text-xs tracking-wide capitalize opacity-90">{{ weather.weather[0].description }}</p>
                            <p class="mt-1 text-[11px] opacity-80">{{ weather.name }}, {{ weather.sys.country }}</p>
                            <p class="mt-2 text-[11px] opacity-90">
                                💨 {{ weather.wind.speed }} m/s · 💧 {{ weather.main.humidity }}%
                            </p>
                        </div>
                        <img
                            class="size-10 shrink-0"
                            :src="`https://openweathermap.org/img/wn/${weather.weather[0].icon}@2x.png`"
                            alt=""
                        />
                    </div>
                </div>
                <div
                    v-else
                    class="relative h-36 overflow-hidden rounded-xl border border-sidebar-border/70 bg-neutral-50/60 p-4 dark:bg-neutral-800/40 dark:border-sidebar-border"
                >
                    <!-- subtle background pattern when no data -->
                    <PlaceholderPattern />

                    <div class="relative z-10 flex h-full flex-col justify-between">
                        <div>
                            <div class="h-6 w-20 animate-pulse rounded bg-neutral-200/60 dark:bg-neutral-700/60"></div>
                            <div class="mt-2 h-4 w-36 animate-pulse rounded bg-neutral-200/40 dark:bg-neutral-700/40"></div>
                            <div class="mt-2 h-3 w-44 animate-pulse rounded bg-neutral-200/30 dark:bg-neutral-700/30"></div>
                        </div>
                        <div class="mt-2">
                            <div class="h-4 w-52 animate-pulse rounded bg-neutral-200/30 dark:bg-neutral-700/30"></div>
                            <p class="mt-3 text-[11px] opacity-70">
                                Weather unavailable. Add <span class="font-semibold">WEATHER_API</span>.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-2 rounded-xl border border-sidebar-border/70 bg-background/30 p-3 dark:border-sidebar-border">
                <div class="text-xs font-semibold opacity-90">Search weather</div>
                <form
                    class="flex flex-col gap-2 sm:flex-row sm:items-end"
                    @submit.prevent="searchWeather()"
                >
                    <div class="flex-1">
                        <label class="mb-1 block text-[11px] opacity-80" for="location-input-compact">Location</label>
                        <Input
                            id="location-input-compact"
                            v-model="location"
                            placeholder="e.g. Tallinn, EE"
                            class="h-8"
                        />
                    </div>
                    <Button type="submit" size="sm" variant="secondary" class="shrink-0">
                        Search
                    </Button>
                </form>
            </div>

        </div>
    </AppLayout>
</template>
