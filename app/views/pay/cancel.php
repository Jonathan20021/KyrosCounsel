<div class="min-h-screen flex items-center justify-center px-4 py-12 bg-slate-50">
    <div class="w-full max-w-md text-center">
        <div class="mx-auto h-16 w-16 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center text-3xl mb-4">⚠️</div>
        <h1 class="text-2xl font-bold">Pago cancelado</h1>
        <p class="mt-2 text-slate-600">No se procesó ningún cargo.</p>
        <a href="<?= e(url('pay/' . $link['token'])) ?>" class="mt-6 inline-block rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white px-5 py-2.5 text-sm font-semibold">Volver e intentar de nuevo</a>
    </div>
</div>
