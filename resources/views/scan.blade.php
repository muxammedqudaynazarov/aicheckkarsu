@extends('layouts.app')
@section('content')
    <button type="button" class="btn btn-primary" onclick="scanMultiplePages()">
        <i class="fas fa-print mr-2"></i> Ko'p sahifali skanerlash (PDF)
    </button>

    <div id="scanResultBlock" style="display: none;" class="mt-3">
        <div class="alert alert-success" id="scanMessage"></div>
        <iframe id="scannedPdfPreview" src="" width="100%" height="500px"
                style="border: 1px solid #ddd; display: none;"></iframe>
    </div>
@endsection
@section('scripting')
    <script src="https://cdn.asprise.com/scannerjs/scanner.js" type="text/javascript"></script>
    <script>
        function scanMultiplePages() {
            scanner.scan(displayPdfOnPage, {
                "output_settings": [
                    {
                        "type": "return-base64",
                        "format": "pdf" // JPG o'rniga PDF qilamiz
                    }
                ]
            });
        }

        function displayPdfOnPage(successful, mesg, response) {
            if (!successful) {
                alert('Skanerlashda xatolik: ' + mesg);
                return;
            }

            if (successful && mesg != null && mesg.toLowerCase().indexOf('user cancel') >= 0) {
                console.info('Foydalanuvchi bekor qildi');
                return;
            }

            // Response dan Base64 PDF ni ajratib olamiz
            // Scanner.js PDF uchun data-uri qaytarishi mumkin yoki faqat Base64 string o'zini.
            // Odatda response matni ichida keladi.
            let pdfBase64 = null;

            // Asprise formatida odatda 'response.output' ichida keladi
            if (response.output && response.output.length > 0) {
                pdfBase64 = response.output[0].base64;
            } else {
                // Agar format boshqacha bo'lsa, xatolik beramiz
                alert('PDF formatida ma\'lumot olinmadi.');
                return;
            }

            if (pdfBase64) {
                // PDF ni ekranda ko'rsatish
                let pdfDataUri = "data:application/pdf;base64," + pdfBase64;
                document.getElementById('scannedPdfPreview').src = pdfDataUri;
                document.getElementById('scannedPdfPreview').style.display = 'block';
                document.getElementById('scanResultBlock').style.display = 'block';

                // Serverga yuborish (data-uri prefiksini qo'shib yuboramiz)
                uploadToServer(pdfDataUri);
            }
        }

        function uploadToServer(base64String) {
            let csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            document.getElementById('scanMessage').innerHTML = 'Skanerlandi! Serverga yuklanmoqda... <i class="fas fa-spinner fa-spin"></i>';

            fetch("{{ route('scan.upload') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    file_data: base64String, // Endi image_data emas, file_data deb nomladim
                    lesson_id: 1,
                    student_id: 1
                })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('scanMessage').innerHTML = 'Muvaffaqiyatli yuklandi!';
                    } else {
                        alert('Yuklashda xatolik: ' + data.message);
                        document.getElementById('scanMessage').innerHTML = 'Yuklashda xatolik yuz berdi.';
                    }
                })
                .catch(error => {
                    console.error('Xatolik:', error);
                    alert('Server bilan ulanishda xatolik!');
                });
        }
    </script>
@endsection
