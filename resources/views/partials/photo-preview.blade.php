{{--
    Show the picked photo straight away, before Save Changes.

    Choosing a file used to change nothing on screen: the file dialog closed and
    the old picture stayed, so there was no way to tell whether the click had
    registered, whether the right file had been picked, or how it would be
    cropped. The answer only arrived after a save — and a save is the wrong
    place to learn you chose the wrong file.

    Reads the file in the browser. Nothing is uploaded here; the form still
    carries the file, and Save Changes is still what writes it.

    Expects: $input  — id of the <input type="file">
             $avatar — id of the round container holding the photo or the icon
--}}
@once
@push('scripts')
<script>
    window.pesoPhotoPreview = function (inputId, avatarId) {
        const input  = document.getElementById(inputId);
        const avatar = document.getElementById(avatarId);
        if (!input || !avatar) return;

        input.addEventListener('change', function () {
            const file = this.files && this.files[0];
            if (!file) return;

            // Not an image, or unreadable: leave what is on screen alone and
            // let the server's own validation answer on submit.
            if (!file.type.startsWith('image/')) return;

            const reader = new FileReader();
            reader.onload = function (e) {
                let img = avatar.querySelector('img');
                if (!img) {
                    avatar.innerHTML = '';
                    img = document.createElement('img');
                    img.alt = 'Profile photo';
                    img.style.width     = '100%';
                    img.style.height    = '100%';
                    img.style.objectFit = 'cover';
                    avatar.appendChild(img);
                }
                img.src = e.target.result;
            };
            reader.readAsDataURL(file);
        });
    };
</script>
@endpush
@endonce

@push('scripts')
<script>
    window.pesoPhotoPreview(@json($input), @json($avatar));
</script>
@endpush
