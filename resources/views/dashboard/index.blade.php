@section('page-title', 'Dashboard')
<x-default-layout>
    <div class="container px-6 py-4 mx-auto">

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 gap-6 md:grid-cols-5 mb-6">
            <!-- Card Barang -->
            <div class="flex items-center bg-white p-5 rounded-lg shadow-sm">
                <div class="flex-1">
                    <h2 class="text-gray-600 text-sm font-medium mb-1">Barang</h2>
                    <p class="text-2xl font-bold">54</p>
                </div>
                <div class="p-3 rounded-full bg-yellow-100">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                    </svg>
                </div>
            </div>

            <!-- Card Supplier -->
            <div class="flex items-center bg-white p-6 rounded-lg shadow-sm">
                <div class="flex-1">
                    <h2 class="text-gray-600 text-sm font-medium mb-1">Supplier</h2>
                    <p class="text-2xl font-bold">23</p>
                </div>
                <div class="p-3 rounded-full bg-green-100">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
            </div>

            <!-- Card TBA -->
            <div class="flex items-center bg-white p-6 rounded-lg shadow-sm">
                <div class="flex-1">
                    <h2 class="text-gray-600 text-sm font-medium mb-1">TBA</h2>
                    <p class="text-2xl font-bold">23</p>
                </div>
                <div class="p-3 rounded-full bg-green-100">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
            </div>

            <!-- Card TBA -->
            <div class="flex items-center bg-white p-6 rounded-lg shadow-sm">
                <div class="flex-1">
                    <h2 class="text-gray-600 text-sm font-medium mb-1">TBA</h2>
                    <p class="text-2xl font-bold">23</p>
                </div>
                <div class="p-3 rounded-full bg-green-100">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
            </div>

            <!-- Card TBA -->
            <div class="flex items-center bg-white p-6 rounded-lg shadow-sm">
                <div class="flex-1">
                    <h2 class="text-gray-600 text-sm font-medium mb-1">TBA</h2>
                    <p class="text-2xl font-bold">23</p>
                </div>
                <div class="p-3 rounded-full bg-green-100">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Main content -->
        <div class="grid grid-cols-1 md:grid-cols-1 gap-6">
            <div class="max-w-full bg-white rounded-lg shadow-sm dark:bg-white p-4 md:p-6">
                {{-- Header --}}
                <div class="flex justify-between mb-5">
                    <div>
                        <h2 class="leading-none text-lg font-semibold text-black pb-1">Activities</h2>
                        <p class="text-base font-normal text-gray-500 dark:text-gray-400">Barang Masuk & Keluar</p>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="text-sm text-gray-500 dark:text-gray-400">29/03/2025</span>
                        <button
                            id="dropdownDefaultButton"
                            data-dropdown-toggle="lastDaysdropdown"
                            data-dropdown-placement="bottom"
                            class="px-2.5 py-1.5 border border-gray-300 dark:border-gray-600 text-sm rounded-lg flex items-center text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                            Hari ini
                            <svg class="w-3 h-3 ml-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        {{-- Dropdown (opsional aktifkan pakai Flowbite/JS lain) --}}
                        <div id="lastDaysdropdown"
                            class="z-10 hidden bg-white divide-y divide-gray-100 rounded-lg shadow-sm w-44 dark:bg-gray-700">
                            <ul class="py-2 text-sm text-gray-700 dark:text-gray-200" aria-labelledby="dropdownDefaultButton">
                                <li><a href="#" class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600">Yesterday</a></li>
                                <li><a href="#" class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600">Today</a></li>
                                <li><a href="#" class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600">Last 7 days</a></li>
                                <li><a href="#" class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600">Last 30 days</a></li>
                            </ul>
                        </div>
                    </div>
                </div>

                {{-- Chart Area --}}
                <div class="h-64 w-full">
                    <div id="activitiesChart" class="w-full h-full"></div>
                </div>

                {{-- Footer Legend --}}
                <div class="flex justify-between items-center border-t border-gray-200 dark:border-gray-700 pt-5 mt-5">
                    <div class="flex items-center space-x-6">
                        <div class="flex items-center">
                            <span class="w-3 h-3 rounded-full bg-green-500 mr-2"></span>
                            <span class="text-sm text-gray-600 dark:text-gray-300">Barang Masuk</span>
                        </div>
                        <div class="flex items-center">
                            <span class="w-3 h-3 rounded-full bg-red-500 mr-2"></span>
                            <span class="text-sm text-gray-600 dark:text-gray-300">Barang Keluar</span>
                        </div>
                    </div>
                </div>
            </div>


            <!-- Stock Running Low -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold mb-4">Stock Running Low</h2>
                <div class="space-y-4">
                    @for($i = 1; $i <= 6; $i++)
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <span class="text-gray-500 mr-2">#{{ $i }}</span>
                            <div>
                                <p class="font-medium text-sm">iPhone 16 1TB Series...</p>
                                <span class="text-xs text-gray-500">Stock: <span class="text-red-500">1</span></span>
                            </div>
                        </div>
                    </div>
                    @endfor
                    <a href="#" class="text-blue-500 text-sm hover:underline flex justify-center">Lihat Stock Barang</a>
                </div>
            </div>

            <!-- Activity Log -->
            <div class="bg-white rounded-lg shadow-sm p-6 md:col-span-3">
                <h2 class="text-lg font-medium mb-4">Activity Log</h2>
                <div class="space-y-3">
                    <div class="flex items-center justify-between bg-white p-4 rounded-lg">
                        <div class="flex items-center">
                            <div class="h-10 w-10 rounded-full bg-gray-200 overflow-hidden mr-3">
                                <img src="https://via.placeholder.com/40" alt="Admin" class="w-full h-full object-cover">
                            </div>
                            <div>
                                <p class="font-medium">Admin bola</p>
                                <p class="text-sm text-gray-500">menambahkan stock iPhone 19 dengan jumlah 45</p>
                            </div>
                        </div>
                        <span class="text-sm text-gray-500">19:22<br>01/01/2025</span>
                    </div>
                    
                    <!-- Empty activity logs for placeholder -->
                    @for($i = 0; $i < 2; $i++)
                    <div class="flex items-center justify-between bg-gray-100 p-4 rounded-lg">
                        <div class="flex items-center">
                            <div class="h-10 w-10 rounded-full bg-gray-200 overflow-hidden mr-3">
                                <img src="https://via.placeholder.com/40" alt="Admin" class="w-full h-full object-cover">
                            </div>
                            <div class="h-4 bg-gray-200 rounded w-64"></div>
                        </div>
                        <div class="h-4 bg-gray-200 rounded w-20"></div>
                    </div>
                    @endfor
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var options = {
                chart: {
                    type: 'line',
                    height: '100%',
                    toolbar: {
                        show: false
                    }
                },
                series: [{
                    name: 'Barang Masuk',
                    data: [0, 8, 5, 2, 0, 5, 0, 8, 0]
                }, {
                    name: 'Barang Keluar',
                    data: [0, 5, 10, 8, 0, 8, 0, 10, 10]
                }],
                colors: ['#10B981', '#EF4444'],
                stroke: {
                    curve: 'smooth',
                    width: 2
                },
                xaxis: {
                    categories: ['00:00', '03:00', '06:00', '09:00', '12:00', '15:00', '18:00', '21:00', '23:59'],
                },
                yaxis: {
                    min: 0,
                    max: 200,
                    tickAmount: 4,
                    labels: {
                        formatter: function(val) {
                            return val.toFixed(0);
                        }
                    }
                },
                grid: {
                    borderColor: '#f1f1f1',
                    strokeDashArray: 5,
                    xaxis: {
                        lines: {
                            show: true
                        }
                    },
                    yaxis: {
                        lines: {
                            show: true
                        }
                    }
                },
                legend: {
                    show: false
                },
                tooltip: {
                    shared: true,
                    intersect: false
                }
            };

            var chart = new ApexCharts(document.querySelector("#activitiesChart"), options);
            chart.render();
        });
    </script>
    @endpush
</x-default-layout>