<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoices extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id', 'company_id', 'invoice_number', 'invoice_date', 'due_date',
        'subtotal', 'tax', 'total', 'status', 'notes', 'currency',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Clients::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Companies::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(Invoice_items::class, 'invoice_id');
    }

    public function calculateTotal(): void
    {
        $this->subtotal = $this->items()->sum('total');
        $this->total = $this->subtotal + $this->tax;
        $this->save();
    }
}
