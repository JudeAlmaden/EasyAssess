<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { CreditCard, Crown, Star, Gift } from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Payment', href: '/payment' },
];

const plans = [
    {
        id: 'free',
        name: 'Free',
        price: '₱0',
        icon: Gift,
        benefits: [
            'Up to 10 recordings per month',
            'Basic analytics view',
        ],
    },
    {
        id: 'basic',
        name: 'Basic',
        price: '₱50 / month or ₱300 / year',
        icon: CreditCard,
        benefits: [
            'Unlimited recordings',
            'Access to analytics export',
        ],
    },
    {
        id: 'premium',
        name: 'Premium',
        price: '₱75 / month or ₱550 / year',
        icon: Crown,
        benefits: [
            'Unlimited recordings',
            'Access to analytics export',
            'Assessment sharing',
            'Priority support',
        ],
    },
];

function selectPlan(planId: string) {
    console.log('Selected plan:', planId);
    // Payment or subscription API call here
}
</script>

<template>
    <Head title="Payment" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="max-w-4xl mx-auto mt-8">
            <h1 class="text-2xl font-bold mb-6 text-center">Choose Your Subscription</h1>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div
                    v-for="plan in plans"
                    :key="plan.id"
                    class="border rounded-2xl p-6 flex flex-col justify-between shadow hover:shadow-lg transition cursor-pointer bg-white"
                    @click="selectPlan(plan.id)"
                >
                    <div>
                        <component :is="plan.icon" class="w-10 h-10 text-blue-600 mb-4" />
                        <h2 class="text-xl font-semibold mb-2">{{ plan.name }}</h2>
                        <div class="text-2xl font-bold mb-4">{{ plan.price }}</div>
                        <ul class="space-y-2 text-gray-600">
                            <li v-for="(benefit, index) in plan.benefits" :key="index" class="flex items-start gap-2">
                                <Star class="w-4 h-4 text-green-500 mt-1" />
                                <span>{{ benefit }}</span>
                            </li>
                        </ul>
                    </div>
                    <button
                        class="mt-6 bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-lg"
                    >
                        {{ plan.id === 'free' ? 'Get Started' : 'Subscribe' }}
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
