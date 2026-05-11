<?php

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('customers')
            ->whereNotNull('address')
            ->lazyById()
            ->each(function (object $customer): void {
                try {
                    Crypt::decryptString($customer->address);

                    return;
                } catch (DecryptException) {
                    DB::table('customers')
                        ->where('id', $customer->id)
                        ->update(['address' => Crypt::encryptString($customer->address)]);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('customers')
            ->whereNotNull('address')
            ->lazyById()
            ->each(function (object $customer): void {
                try {
                    $address = Crypt::decryptString($customer->address);
                } catch (DecryptException) {
                    return;
                }

                DB::table('customers')
                    ->where('id', $customer->id)
                    ->update(['address' => $address]);
            });
    }
};
