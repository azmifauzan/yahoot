import Swal from 'sweetalert2';

const defaultOptions = {
    customClass: {
        popup: 'swal-yahoot-popup',
        confirmButton: 'swal-yahoot-confirm',
        cancelButton: 'swal-yahoot-cancel',
        denyButton: 'swal-yahoot-deny',
    },
    buttonsStyling: false,
};

export function useSwal() {
    function toast(message, icon = 'success') {
        return Swal.fire({
            toast: true,
            position: 'top-end',
            icon,
            title: message,
            showConfirmButton: false,
            timer: 2500,
            timerProgressBar: true,
        });
    }

    function confirm({ title, text, confirmText = 'Ya', cancelText = 'Batal', icon = 'warning' }) {
        return Swal.fire({
            ...defaultOptions,
            title,
            text,
            icon,
            showCancelButton: true,
            confirmButtonText: confirmText,
            cancelButtonText: cancelText,
            reverseButtons: true,
        });
    }

    function success(title, text = '') {
        return Swal.fire({
            ...defaultOptions,
            title,
            text,
            icon: 'success',
            confirmButtonText: 'OK',
        });
    }

    function error(title, text = '') {
        return Swal.fire({
            ...defaultOptions,
            title,
            text,
            icon: 'error',
            confirmButtonText: 'OK',
        });
    }

    return { toast, confirm, success, error, Swal };
}
