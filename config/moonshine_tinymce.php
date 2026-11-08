<?php

$adminPrefix = env('MOONSHINE_ROUTE_PREFIX', 'admin');

/*
 * Кнопка «media» в редакторі сама файли не вантажить — вона лише питає URL.
 * file_picker_callback відкриває звичайний вибір файлу, шле його на
 * /<admin>/editor/upload-video (App\Http\Controllers\Admin\EditorVideoUploadController)
 * і віддає редактору шлях, який той підставляє у <video>.
 *
 * Рядок компілюється через new Function('return ' + …) у пакеті
 * moonshine/tinymce (resources/js/init.js), тож це має бути вираз-функція.
 * CSRF-токен беремо з прихованого поля форми: meta csrf-token у макеті
 * адмінки немає, а _token є на кожній сторінці-формі.
 */
$filePickerCallback = <<<JS
(cb, value, meta) => {
    if (meta.filetype !== 'media') { return; }

    const input = document.createElement('input');
    input.type = 'file';
    input.accept = 'video/mp4,video/webm';

    input.onchange = () => {
        const file = input.files && input.files[0];
        if (!file) { return; }

        const body = new FormData();
        body.append('file', file);

        fetch('/{$adminPrefix}/editor/upload-video', {
            method: 'POST',
            body: body,
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('input[name=_token]')?.value || '',
                'X-Requested-With': 'XMLHttpRequest',
            },
        })
            .then((response) => response.json().then((data) => ({ ok: response.ok, data })))
            .then(({ ok, data }) => {
                if (!ok) {
                    const message = (data.errors && data.errors.file && data.errors.file[0]) || data.message || 'Не вдалося завантажити відео';
                    tinymce.activeEditor.windowManager.alert(message);
                    return;
                }
                cb(data.location, { title: file.name });
            })
            .catch(() => tinymce.activeEditor.windowManager.alert('Не вдалося завантажити відео'));
    };

    input.click();
}
JS;

return [
    'token' => env('TINYMCE_TOKEN', ''),
    'plugins' => [
        'anchor', 'autolink', 'autoresize', 'charmap', 'codesample', 'code', 'emoticons', 'image', 'link',
        'lists', 'advlist', 'media', 'searchreplace', 'table', 'wordcount', 'directionality',
        'fullscreen', 'help', 'nonbreaking', 'pagebreak', 'preview', 'visualblocks', 'visualchars',
    ],
    'menubar' => 'file edit insert view format table tools',
    'toolbar' => 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | '
        .'link image media table tabledelete hr nonbreaking pagebreak | align lineheight | '
        .'numlist bullist indent outdent | emoticons charmap | removeformat | codesample | ltr rtl | '
        .'tableprops tablerowprops tablecellprops | tableinsertrowbefore tableinsertrowafter tabledeleterow | '
        .'tableinsertcolbefore tableinsertcolafter tabledeletecol | '
        .'fullscreen preview print visualblocks visualchars code | help',
    'options' => [
        'file_picker_types' => 'media',
        'media_live_embeds' => true,
    ],
    'callbacks' => [
        'file_picker_callback' => $filePickerCallback,
    ],
];
