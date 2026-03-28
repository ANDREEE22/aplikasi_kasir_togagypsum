<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Tambah Produk Baru') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 p-6 shadow-sm sm:rounded-lg">
                
                <form action="{{ route('admin.products.store') }}" method="POST">
                    @csrf
                    
                    <div class="mb-4">
                        <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">
                            ID Produk Custom (Opsional)
                        </label>
                        <input type="text" name="custom_id" 
                            class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm w-full mt-1">
                    </div>
                    
                    <div class="mb-4">
                        <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">
                            Nama Produk
                        </label>
                        <input type="text" name="name" 
                            class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm w-full mt-1" 
                            required>
                    </div>

                    <div class="mb-4">
                        <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">
                            Kategori (Opsional)
                        </label>
                        <input type="text" name="category" 
                            class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm w-full mt-1">
                    </div>

                    <div class="mb-4">
                        <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">
                            Panjang / Diameter (Opsional)
                        </label>
                        <input type="text" name="length_diameter" 
                            class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm w-full mt-1">
                    </div>

                    <div class="mb-4">
                        <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">
                            Lebar (Opsional)
                        </label>
                        <input type="text" name="width" 
                            class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm w-full mt-1">
                    </div>

                    <div class="grid grid-cols-3 gap-4 mb-4">
                        <div>
                            <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">
                                Harga Normal (Rp)
                            </label>
                            <input type="number" name="price_normal" 
                                class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm w-full mt-1" 
                                required>
                        </div>

                        <div>
                            <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">
                                Harga Sedang (Rp)
                            </label>
                            <input type="number" name="price_medium" 
                                class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm w-full mt-1" 
                                required>
                        </div>

                        <div>
                            <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">
                                Harga Tinggi (Rp)
                            </label>
                            <input type="number" name="price_high" 
                                class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm w-full mt-1" 
                                required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">
                            Stok Awal
                        </label>
                        <input type="number" name="stock" 
                            class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm w-full mt-1" 
                            required>
                    </div>

                    <div class="mb-4">
                        <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">
                            Deskripsi (Opsional)
                        </label>
                        <textarea name="description" rows="3"
                            class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm w-full mt-1"></textarea>
                    </div>

                    <div class="flex items-center justify-end mt-4">
                        <a href="{{ route('admin.products.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 underline mr-4">
                            Batal
                        </a>
                        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-500 transition duration-150 ease-in-out">
                            Simpan Produk
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>