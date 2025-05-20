@section('page-title', 'Dashboard')
<x-default-layout>
    <div class="container px-6 py-4 mx-auto">

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 gap-6 md:grid-cols-5 mb-6">
            <!-- Card Kategori -->
            <div class="flex items-center bg-white p-6 rounded-lg shadow-sm">
                <div class="flex-1">
                    <h2 class="text-gray-600 text-sm font-medium mb-1">Kategori Barang</h2>
                    <p class="text-2xl font-bold">23</p>
                </div>
                <div class="p-3 rounded-full bg-purple-100">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                      <path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5m6 4.125 2.25 2.25m0 0 2.25 2.25M12 13.875l2.25-2.25M12 13.875l-2.25 2.25M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
                    </svg>
                </div>
            </div>

            <!-- Card Barang -->
            <div class="flex items-center bg-white p-5 rounded-lg shadow-sm">
                <div class="flex-1">
                    <h2 class="text-gray-600 text-sm font-medium mb-1">Barang</h2>
                    <p class="text-2xl font-bold">54</p>
                </div>
                <div class="p-3 rounded-full bg-yellow-100">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
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
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                       <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                    </svg>
                </div>
            </div>

            <!-- Card Gudang -->
            <div class="flex items-center bg-white p-6 rounded-lg shadow-sm">
                <div class="flex-1">
                    <h2 class="text-gray-600 text-sm font-medium mb-1">Gudang</h2>
                    <p class="text-2xl font-bold">23</p>
                </div>
                <div class="p-3 rounded-full bg-blue-100">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                    </svg>
                </div>
            </div>

            <!-- Card Toko -->
            <div class="flex items-center bg-white p-6 rounded-lg shadow-sm">
                <div class="flex-1">
                    <h2 class="text-gray-600 text-sm font-medium mb-1">Toko</h2>
                    <p class="text-2xl font-bold">23</p>
                </div>
                <div class="p-3 rounded-full bg-pink-100">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349m0 0a3.001 3.001 0 0 0 3.75-.615A2.993 2.993 0 0 0 9.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 0 0 2.25 1.016c.896 0 1.7-.393 2.25-1.015a3.001 3.001 0 0 0 3.75.614m-16.5 0a3.004 3.004 0 0 1-.621-4.72l1.189-1.19A1.5 1.5 0 0 1 5.378 3h13.243a1.5 1.5 0 0 1 1.06.44l1.19 1.189a3 3 0 0 1-.621 4.72M6.75 18h3.75a.75.75 0 0 0 .75-.75V13.5a.75.75 0 0 0-.75-.75H6.75a.75.75 0 0 0-.75.75v3.75c0 .414.336.75.75.75Z" />
                    </svg>

                </div>
            </div>

            
        </div>

        <!-- Main content -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Activities -->
            <div class="lg:col-span-2 max-w-full bg-white rounded-lg shadow-sm dark:bg-white p-4 md:p-6">
                <!-- Header -->
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
                        <!-- Dropdown -->
                        <div id="lastDaysdropdown"
                            class="z-10 hidden bg-white divide-y divide-gray-100 rounded-lg shadow-sm w-44 dark:bg-gray-700">
                            <ul class="py-2 text-sm text-gray-700 dark:text-gray-200" aria-labelledby="dropdownDefaultButton">
                                <li><a href="#" class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600">Last 7 days</a></li>
                                <li><a href="#" class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600">Last 30 days</a></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Chart -->
                <div class="h-64 w-full">
                    <div id="labels-chart" class="w-full h-full"></div>
                </div>

                <div class="flex justify-between items-center border-t border-gray-200 dark:border-gray-700 pt-5 mt-5">
                    <div class="flex items-center space-x-6">
                        <div class="flex items-center">
                            <span class="w-3 h-3 rounded-full mr-2" style="background-color: #1A56DB;"></span>
                            <span class="text-sm text-gray-600 dark:text-gray-300">Barang Masuk</span>
                        </div>
                        <div class="flex items-center">
                            <span class="w-3 h-3 rounded-full mr-2" style="background-color: #7E3BF2;"></span>
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
        </div>

        <!-- JS Graf -->
        <!-- Tambahkan di akhir halaman -->
        <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
        <script>
            const options = {
                xaxis: {
                    show: true,
                    categories: ['01 Feb', '02 Feb', '03 Feb', '04 Feb', '05 Feb', '06 Feb', '07 Feb'],
                    labels: {
                        show: true,
                        style: {
                            fontFamily: "Inter, sans-serif",
                            cssClass: 'text-xs font-normal fill-gray-500 dark:fill-gray-400'
                        }
                    },
                    axisBorder: {
                        show: false,
                    },
                    axisTicks: {
                        show: false,
                    },
                },
                yaxis: {
                    show: true,
                    labels: {
                        show: true,
                        style: {
                            fontFamily: "Inter, sans-serif",
                            cssClass: 'text-xs font-normal fill-gray-500 dark:fill-gray-400'
                        },
                        formatter: function (value) {
                            return + value;
                        }
                    }
                },
                series: [
                    {
                        name: "Barang Masuk",
                        data: [150, 141, 145, 152, 135, 125],
                        color: "#1A56DB",
                    },
                    {
                        name: "Barang Keluar",
                        data: [43, 13, 65, 12, 42, 73],
                        color: "#7E3BF2",
                    },
                ],
                chart: {
                    sparkline: {
                        enabled: false
                    },
                    height: "100%",
                    width: "100%",
                    type: "area",
                    fontFamily: "Inter, sans-serif",
                    dropShadow: {
                        enabled: false,
                    },
                    toolbar: {
                        show: false,
                    },
                },
                tooltip: {
                    enabled: true,
                    x: {
                        show: false,
                    },
                },
                fill: {
                    type: "gradient",
                    gradient: {
                        opacityFrom: 0.55,
                        opacityTo: 0,
                        shade: "#1C64F2",
                        gradientToColors: ["#1C64F2"],
                    },
                },
                dataLabels: {
                    enabled: false,
                },
                stroke: {
                    width: 6,
                },
                legend: {
                    show: false
                },
                grid: {
                    show: false,
                },
            };

            if (document.getElementById("labels-chart") && typeof ApexCharts !== 'undefined') {
                const chart = new ApexCharts(document.getElementById("labels-chart"), options);
                chart.render();
            }
        </script>
    </div>
</x-default-layout>