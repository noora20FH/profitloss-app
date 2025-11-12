// ~/utils/helpers.js (Pastikan path-nya benar)

/**
 * Helper untuk format mata uang IDR.
 */
export const formatCurrency = (value) => {
    if (value === 0 || value === undefined || value === null) return "Rp 0,00";
    
    // Pastikan nilai adalah number sebelum format
    const numValue = Number(value); 
    
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 2,
    }).format(numValue);
};

// Anda bisa menambahkan helper lain di sini
// export const formatDate = (date) => { ... }