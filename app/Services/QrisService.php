<?php

namespace App\Services;

/**
 * Generator payload QRIS statis (EMVCo TLV) untuk simulasi pembayaran.
 *
 * ponytail: payload dibentuk dari setting merchant + total tagihan,
 * tanpa integrasi bank/switching. Upgrade path: pasang provider QRIS
 * (Midtrans/Xendit/Duitku) — ganti satu service ini, flow tetap sama.
 */
class QrisService
{
    private const CRC_POLY = 0x1021;

    public function __construct(
        private string $merchantName,
        private string $merchantCity,
        private string $merchanId,
    ) {
    }

    /** Bangun dari config/settings. */
    public static function fromSettings(): self
    {
        $name = (string) (\App\Models\Setting::where('key', 'qris_merchant_name')->value('value') ?: config('services.qris.merchant_name', 'OMS UMKM'));
        $city = (string) (\App\Models\Setting::where('key', 'qris_merchant_city')->value('value') ?: config('services.qris.merchant_city', 'PALEMBANG'));
        $id   = (string) (\App\Models\Setting::where('key', 'qris_merchant_id')->value('value') ?: config('services.qris.merchant_id', '9360000000000001'));

        return new self($name, $city, $id);
    }

    /**
     * Payload QRIS dinamis (dengan nominal transaksi).
     * Tag 01 = 12 → dynamic, tag 54 = amount.
     */
    public function dynamicPayload(int $amount, ?int $orderId = null): string
    {
        $tlv = '';
        $tlv .= $this->tag('00', '01');                        // Payload Format Indicator
        $tlv .= $this->tag('01', '12');                        // Point of Initiation: 12 = dynamic (sekali pakai)
        if ($orderId !== null) {
            $tlv .= $this->tag('62', $this->tag('05', (string) $orderId)); // Merchant Account Info (ref order)
        }
        $tlv .= $this->tag('52', '5812');                      // MCC: 5812 = restaurant/cafe
        $tlv .= $this->tag('53', '360');                       // Currency: IDR
        $tlv .= $this->tag('54', (string) $amount);            // Amount
        $tlv .= $this->tag('58', 'ID');                        // Country
        $tlv .= $this->tag('59', mb_substr($this->merchantName, 0, 25)); // Merchant Name
        $tlv .= $this->tag('60', mb_substr($this->merchantCity, 0, 15));          // Merchant City
        $tlv .= $this->tag('63', '04');                        // CRC placeholder (diisi setelah hitung)

        return $tlv . $this->crc16($tlv);
    }

    private function tag(string $id, string $value): string
    {
        return $id . str_pad((string) strlen($value), 2, '0', STR_PAD_LEFT) . $value;
    }

    /** CRC-16/CCITT-FALSE, hex uppercase, 4 digit. */
    private function crc16(string $data): string
    {
        $crc = 0xFFFF;
        foreach (str_split($data) as $byte) {
            $crc ^= ord($byte) << 8;
            for ($i = 0; $i < 8; $i++) {
                $crc = ($crc & 0x8000) ? (($crc << 1) ^ self::CRC_POLY) : ($crc << 1);
                $crc &= 0xFFFF;
            }
        }
        return strtoupper(str_pad(dechex($crc), 4, '0', STR_PAD_LEFT));
    }
}
