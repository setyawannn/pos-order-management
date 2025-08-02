// resources/js/utils/currency.ts
export const formatCurrency = (amount: number): string => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(amount);
};

export const formatPrice = (amount: number): string => {
    return formatCurrency(amount).replace('Rp', 'IDR');
};
