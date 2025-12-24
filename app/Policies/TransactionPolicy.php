<?php

namespace App\Policies;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TransactionPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->role === 'admin' || $user->role === 'finance' || $user->role === 'cashier';
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Transaction $transaction): bool
    {
        // Finance can view all transactions
        if ($user->role === 'finance') {
            return true;
        }
        
        // Admin can view all transactions
        if ($user->role === 'admin') {
            return true;
        }
        
        // Cashier can only view their own transactions
        if ($user->role === 'cashier') {
            return $user->id === $transaction->user_id;
        }
        
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->role === 'admin' || $user->role === 'cashier';
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Transaction $transaction): bool
    {
        // Only admin can update transactions
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Transaction $transaction): bool
    {
        // Only admin can delete transactions
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can void the transaction.
     */
    public function void(User $user, Transaction $transaction): bool
    {
        // Only admin can void transactions
        return $user->role === 'admin' && $transaction->status !== 'void';
    }
}