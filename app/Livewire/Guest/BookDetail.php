<?php

namespace App\Livewire\Guest;

use App\Models\Book;
use Livewire\Component;

class BookDetail extends Component
{
    public Book $book;

    public function mount(Book $book): void
    {
        $this->book = $book->load('category');
    }

    public function render()
    {
        return view('livewire.guest.book-detail')->layout('layouts.guest', ['title' => $this->book->judul]);
    }
}
