<script setup lang="ts">
import DashboardLayout from '@/layouts/DashboardLayout.vue';
import { Head } from '@inertiajs/vue3';
import { BarChart3, DollarSign, ShoppingBag, Users } from 'lucide-vue-next';

defineOptions({
    layout: DashboardLayout,
});
</script>

<template>
    <Head title="Admin Dashboard" />

    <div class="space-y-6 p-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Dashboard</h1>
            <div class="flex items-center space-x-2">
                <span class="text-sm text-slate-500">Overview</span>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Total Revenue</p>
                        <p class="mt-2 text-3xl font-bold text-slate-900">Rp. 45.231.890</p>
                    </div>
                    <div class="rounded-full bg-emerald-100 p-3 text-emerald-600">
                        <DollarSign class="h-6 w-6" />
                    </div>
                </div>
                <div class="mt-4 flex items-center text-sm">
                    <span class="flex items-center font-medium text-emerald-600"> +20.1% </span>
                    <span class="ml-2 text-slate-500">from last month</span>
                </div>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Active Orders</p>
                        <p class="mt-2 text-3xl font-bold text-slate-900">+573</p>
                    </div>
                    <div class="rounded-full bg-blue-100 p-3 text-blue-600">
                        <ShoppingBag class="h-6 w-6" />
                    </div>
                </div>
                <div class="mt-4 flex items-center text-sm">
                    <span class="flex items-center font-medium text-emerald-600"> +201 </span>
                    <span class="ml-2 text-slate-500">since last hour</span>
                </div>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Active Customers</p>
                        <p class="mt-2 text-3xl font-bold text-slate-900">+2350</p>
                    </div>
                    <div class="rounded-full bg-violet-100 p-3 text-violet-600">
                        <Users class="h-6 w-6" />
                    </div>
                </div>
                <div class="mt-4 flex items-center text-sm">
                    <span class="flex items-center font-medium text-emerald-600"> +180.1% </span>
                    <span class="ml-2 text-slate-500">from last month</span>
                </div>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Sales</p>
                        <p class="mt-2 text-3xl font-bold text-slate-900">+12,234</p>
                    </div>
                    <div class="rounded-full bg-amber-100 p-3 text-amber-600">
                        <BarChart3 class="h-6 w-6" />
                    </div>
                </div>
                <div class="mt-4 flex items-center text-sm">
                    <span class="flex items-center font-medium text-emerald-600"> +19% </span>
                    <span class="ml-2 text-slate-500">from last month</span>
                </div>
            </div>
        </div>

        <!-- Recent Activity / Charts Placeholder -->
        <div class="grid gap-6 lg:grid-cols-2">
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-slate-900">Recent Orders</h3>
                <div class="mt-4 h-64 flex items-center justify-center rounded-lg border border-dashed border-slate-300 bg-slate-50">
                    <p class="text-slate-500">Order list placeholder</p>
                </div>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-slate-900">Sales Overview</h3>
                <div class="mt-4 h-64 flex items-center justify-center rounded-lg border border-dashed border-slate-300 bg-slate-50">
                    <p class="text-slate-500">Chart placeholder</p>
                </div>
            </div>
        </div>
    </div>
</template>
