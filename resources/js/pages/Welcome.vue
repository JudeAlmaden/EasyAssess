<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLogo from '@/components/AppLogo.vue';
import { Printer, Target, Zap } from 'lucide-vue-next';
import GradientText from '../components/ui/GradientText/GradientText.vue';
</script>

<template>

    <Head title="EasyAssess - Optical Mark Recognition System">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>

    <div
        class="flex min-h-screen flex-col items-center bg-[#FDFDFC] text-[#1b1b18] lg:justify-between lg:p-0 dark:bg-[#0a0a0a] dark:text-[#EDEDEC]">
        <!-- Header -->
        <header class="w-full py-4 border-b border-[#19140020] dark:border-[#3E3E3A]">
            <nav class="flex max-w-6xl mx-auto items-center justify-between px-6">
                <div class="text-xl font-bold">
                    <Link :href="route('dashboard')">
                    <AppLogo />
                    </Link>
                </div>
                <div class="flex gap-4">
                    <Link v-if="$page.props.auth.user" :href="route('dashboard')" class="hover:underline">
                    Dashboard
                    </Link>
                    <template v-else>
                        <Link :href="route('login')" class="hover:underline">Log in</Link>
                        <Link :href="route('register')" class="hover:underline">Register</Link>
                    </template>
                </div>
            </nav>
        </header>

        <!-- Hero -->
        <section
            class="flex flex-col lg:flex-row items-center text-center lg:text-left px-6 py-16 lg:py-24 max-w-6xl mx-auto gap-8">
            <!-- Text -->
            <div class="flex-1">
                <h1 class="text-4xl lg:text-5xl font-extrabold mb-4">
                    EasyAssess: Optical Mark Recognition System
                </h1>
                <p class="text-lg mb-8 text-gray-600 dark:text-gray-300">
                    An Optical Mark Recognition system built for <span class="font-semibold">educators</span> and <span
                        class="font-semibold">survey administrators</span> — detect answers, grade automatically, and
                    view
                    powerful analytics.
                    <br />
                    <span class="font-semibold text-blue-600 dark:text-blue-400">No special hardware required — and you
                        can
                        start for free.</span>
                </p>
                <div class="flex gap-4 justify-center lg:justify-start">
                    <Link :href="route('register')"
                        class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition font-semibold">
                    Start Free
                    </Link>
                    <!-- Uncomment when is available -->
                    <!-- <Link
                        :href="route('payment')"
                        class="border border-blue-600 text-blue-600 px-6 py-3 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-900/30 transition"
                    >
                        View Plans
                    </Link> -->
                </div>
            </div>

            <!-- Image Placeholder -->
            <div class="flex-1">
                <div
                    class="w-full aspect-video rounded-lg border-2 flex items-center justify-center text-gray-500 bg-gray-100 dark:bg-gray-800">
                    <img src="/images/banner_using_app.jpg" alt="Placeholder Image"
                        class="w-full h-full object-cover rounded-lg" />
                </div>
            </div>
        </section>

        <!-- Features -->
        <section class="w-full bg-gray-50 dark:bg-[#141414] py-16">
            <div class="max-w-6xl mx-auto px-6 grid gap-10 lg:grid-cols-3 text-center">
                <!-- Feature 1 -->
                <div>
                    <div class="w-full h-40 mb-4 flex items-center justify-center text-gray-500">
                        <Printer class="w-12 h-12" />
                    </div>
                    <h3 class="text-xl font-semibold mb-2">No Special Hardware</h3>
                    <p class="text-gray-600 dark:text-gray-400">
                        Works with your current printer, regular paper, and any decent camera — ideal for both classroom
                        exams
                        and field surveys.
                    </p>
                </div>

                <!-- Feature 2 -->
                <div>
                    <div class="w-full h-40 mb-4 flex items-center justify-center text-gray-500">
                        <Target class="w-12 h-12" />
                    </div>
                    <h3 class="text-xl font-semibold mb-2">Accurate Detection</h3>
                    <p class="text-gray-600 dark:text-gray-400">
                        Grid-based OMR scanning ensures reliable bubble recognition for quizzes, standardized tests, and
                        survey
                        forms.
                    </p>
                </div>

                <!-- Feature 3 -->
                <div>
                    <div class="w-full h-40 mb-4 flex items-center justify-center text-gray-500">
                        <Zap class="w-12 h-12" />
                    </div>
                    <h3 class="text-xl font-semibold mb-2">Instant Grading</h3>
                    <p class="text-gray-600 dark:text-gray-400">
                        Automatic scoring of MCQs, blanks, and survey responses in seconds.
                    </p>
                </div>
            </div>
        </section>


        <!-- Pricing Teaser -->
        <!-- <section class="py-16 px-6 max-w-6xl mx-auto text-center">
            <h2 class="text-2xl font-bold mb-4">Simple, Affordable Pricing — with a Free Option</h2>
            <p class="text-gray-600 dark:text-gray-400 mb-8">Start free, upgrade only if you need more features.</p>
            <div class="flex flex-col sm:flex-row gap-6 justify-center">
                <div class="border rounded-lg p-6 w-full sm:w-64 shadow hover:shadow-lg transition border-green-500">
                    <h3 class="font-semibold text-lg mb-2">Free</h3>
                    <p class="text-3xl font-bold mb-4">₱0<span class="text-sm text-gray-500">/mo</span></p>
                    <p class="text-gray-600 dark:text-gray-400 text-sm mb-6">Great for small classes or surveys — all core grading tools included.</p>
                    <Link :href="route('register')" class="bg-green-600 text-white px-4 py-2 rounded-lg block hover:bg-green-700">Get Started</Link>
                </div>
                <div class="border rounded-lg p-6 w-full sm:w-64 shadow hover:shadow-lg transition">
                    <h3 class="font-semibold text-lg mb-2">Basic</h3>
                    <p class="text-3xl font-bold mb-4">₱50<span class="text-sm text-gray-500">/mo</span></p>
                    <p class="text-gray-600 dark:text-gray-400 text-sm mb-6">Unlimited recordings & analytics export</p>
                    <Link :href="route('payment')" class="bg-blue-600 text-white px-4 py-2 rounded-lg block hover:bg-blue-700">Subscribe</Link>
                </div>
                <div class="border rounded-lg p-6 w-full sm:w-64 shadow hover:shadow-lg transition border-yellow-400">
                    <h3 class="font-semibold text-lg mb-2">Premium</h3>
                    <p class="text-3xl font-bold mb-4">₱75<span class="text-sm text-gray-500">/mo</span></p>
                    <p class="text-gray-600 dark:text-gray-400 text-sm mb-6">All Basic features + Assessment sharing</p>
                    <Link :href="route('payment')" class="bg-yellow-400 text-black px-4 py-2 rounded-lg block hover:bg-yellow-500">Go Premium</Link>
                </div>
            </div>
        </section> -->

        <!-- Footer -->
        <footer class="w-full py-6 border-t border-[#19140020] dark:border-[#3E3E3A] text-sm text-center text-gray-500">
            © 2025 EasyAssess. All rights reserved.
        </footer>
    </div>
</template>
