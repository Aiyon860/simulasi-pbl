<button data-drawer-target="sidebar-multi-level-sidebar" data-drawer-toggle="sidebar-multi-level-sidebar" aria-controls="sidebar-multi-level-sidebar" type="button"
    class="absolute top-2 left-2 p-2 text-sm text-gray-500 rounded-lg md:hidden focus:outline-none focus:ring-4 focus:ring-gray-200  hover:bg-gray-200">
    <span class="sr-only">Open sidebar</span>
    <svg class="w-6 h-6" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20"
        xmlns="http://www.w3.org/2000/svg">
        <path clip-rule="evenodd" fill-rule="evenodd"
            d="M2 4.75A.75.75 0 012.75 4h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 4.75zm0 10.5a.75.75 0 01.75-.75h7.5a.75.75 0 010 1.5h-7.5a.75.75 0 01-.75-.75zM2 10a.75.75 0 01.75-.75h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 10z">
        </path>
    </svg>
</button>

<aside id="sidebar-multi-level-sidebar"
  class="fixed top-0 left-0 z-40 w-64 h-screen transition-transform -translate-x-full lg:translate-x-0 lg:-translate-x-full"
  aria-label="Sidebar">
    <div class=" h-[95%] px-3 py-4 overflow-y-auto bg-[#161A30] rounded-xl shadow-lg mx-auto my-6 w-[95%] flex flex-col">
        <div class="flex items-center mb-4">
            <svg width="41" height="40" viewBox="0 0 41 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M25.529 32.7902L8.708 39.9998L24.5555 22.9637L40.2407 20.1333L25.529 32.7902Z" fill="url(#paint0_linear_4719_3880)"/>
                <path d="M26.0159 5.23365L8.00494 0L24.5555 22.964L40.2407 20.1335L26.0159 5.23365Z" fill="url(#paint1_linear_4719_3880)"/>
                <path d="M24.6095 22.9105L8.70799 40L0 19.5995L8.00486 0L24.6095 22.9105Z" fill="url(#paint2_radial_4719_3880)"/>
                <defs>
                <linearGradient id="paint0_linear_4719_3880" x1="24.4473" y1="23.2842" x2="27.0897" y2="31.9013" gradientUnits="userSpaceOnUse">
                <stop stop-color="#31304D"/>
                <stop offset="0.411667" stop-color="#68697E"/>
                <stop offset="1" stop-color="#B6BBC4"/>
                </linearGradient>
                <linearGradient id="paint1_linear_4719_3880" x1="18.8223" y1="-4.53939" x2="29.0842" y2="22.9966" gradientUnits="userSpaceOnUse">
                <stop stop-color="#31304D"/>
                <stop offset="1" stop-color="#B6BBC4"/>
                </linearGradient>
                <radialGradient id="paint2_radial_4719_3880" cx="0" cy="0" r="1" gradientUnits="userSpaceOnUse" gradientTransform="translate(8.81616 22.3231) rotate(97.3134) scale(19.5449 12.0199)">
                <stop stop-color="#B6BBC4"/>
                <stop offset="1" stop-color="#31304D"/>
                </radialGradient>
                </defs>
                </svg>
                 <!-- Ganti dengan path logo Anda -->
            <span class="text-white text-lg font-semibold pl-1">Gudangku</span> <!-- Judul -->
        </div>
       <ul class="pl-2 flex-grow overflow-y-auto">
          <li>
            <a href="{{ route('dashboard') }}" class="flex items-center p-2  rounded-lg text-white  hover:bg-gray-700 group">
               <svg class="shrink-0 w-5 h-5 text-gray-500 transition duration-75 group-hover: dark:text-gray-400 dark:group-hover:text-white" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path fill-rule="evenodd" clip-rule="evenodd" d="M4 2C2.89543 2 2 2.89543 2 4V8C2 9.10457 2.89543 10 4 10H8C9.10457 10 10 9.10457 10 8V4C10 2.89543 9.10457 2 8 2H4ZM18 10C20.2091 10 22 8.20914 22 6C22 3.79086 20.2091 2 18 2C15.7909 2 14 3.79086 14 6C14 8.20914 15.7909 10 18 10ZM10 18C10 20.2091 8.20914 22 6 22C3.79086 22 2 20.2091 2 18C2 15.7909 3.79086 14 6 14C8.20914 14 10 15.7909 10 18ZM16 14C14.8954 14 14 14.8954 14 16V20C14 21.1046 14.8954 22 16 22H20C21.1046 22 22 21.1046 22 20V16C22 14.8954 21.1046 14 20 14H16Z" fill="#F0ECE5"/>
               </svg>
               <span class="ms-3">Dashboard</span>
            </a>
            <li>
       </ul>
       <x-footer />
    </div>
 </aside>
 
 