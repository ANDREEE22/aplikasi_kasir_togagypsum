<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Kelola Produk') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                
                <a href="{{ route('admin.products.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 mb-4 inline-block">
                    + Tambah Produk Baru
                </a>

                <div class="overflow-x-auto">
                    <table class="w-full border-collapse mt-4 text-left text-sm text-gray-500 dark:text-gray-400">
                        <thead class="bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-200 uppercase font-medium">
                            <tr>
                                <th class="p-3 border-b dark:border-gray-600">Nama Produk</th>
                                <th class="p-3 border-b dark:border-gray-600">Harga</th>
                                <th class="p-3 border-b dark:border-gray-600">Stok</th>
                                <th class="p-3 border-b dark:border-gray-600">Deskripsi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($products as $product)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                <td class="p-3 text-gray-900 dark:text-white font-medium">{{ $product->name }}</td>
                                <td class="p-3 text-indigo-600 dark:text-indigo-400 font-bold">
                                    Rp {{ number_format($product->price, 0, ',', '.') }}
                                </td>
                                <td class="p-3 text-gray-900 dark:text-white">{{ $product->stock }}</td>
                                <td class="p-3 text-gray-900 dark:text-white">{{ $product->description }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="p-4 text-center text-gray-500 dark:text-gray-400">
                                    Belum ada data produk. Klik tombol tambah di atas.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>