<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Traits\Helpers;
use Illuminate\Console\Command;
use Illuminate\Notifications\Notifiable;
use App\Notifications\ProductStockNotify;
use App\Http\Controllers\ProductController;

class check_product_stock_level extends Command
{
    use Helpers, Notifiable;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check_product_stock_level';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check product stock level';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $products = Product::where('status_id', ProductController::getProductStatusId('Enabled'))->get();

        $bar = $this->output->createProgressBar($products->count());
        $products->lazy()->each(function (Product $product) use ($bar) {

            if($product->quantity <= 3)
            {
                $product->user->notify( new ProductStockNotify($product));
            }

            $bar->advance();
        });

        $bar->finish();

    }
}
