<div>
    @foreach ($this->comments as $comment)
        <livewire:commentions::comment
            :key="'comment-' . $comment->id"
            :comment="$comment"
            :mentionables="$mentionables"
        />
    @endforeach
</div>
