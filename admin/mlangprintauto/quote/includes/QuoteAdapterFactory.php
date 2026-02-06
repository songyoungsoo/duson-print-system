<?php
require_once __DIR__ . '/QuoteAdapterInterface.php';
require_once __DIR__ . '/adapters/InsertedAdapter.php';
require_once __DIR__ . '/adapters/StickerAdapter.php';
require_once __DIR__ . '/adapters/NamecardAdapter.php';
require_once __DIR__ . '/adapters/EnvelopeAdapter.php';
require_once __DIR__ . '/adapters/LittleprintAdapter.php';
require_once __DIR__ . '/adapters/MerchandisebondAdapter.php';
require_once __DIR__ . '/adapters/CadarokAdapter.php';
require_once __DIR__ . '/adapters/NcrflambeauAdapter.php';
require_once __DIR__ . '/adapters/MstickerAdapter.php';

class QuoteAdapterFactory
{
    private static $typeMap = [
        'sticker' => 'StickerAdapter',
        'sticker_new' => 'StickerAdapter',
        'inserted' => 'InsertedAdapter',
        'namecard' => 'NamecardAdapter',
        'envelope' => 'EnvelopeAdapter',
        'littleprint' => 'LittleprintAdapter',
        'poster' => 'LittleprintAdapter',
        'merchandisebond' => 'MerchandisebondAdapter',
        'cadarok' => 'CadarokAdapter',
        'leaflet' => 'CadarokAdapter',
        'ncrflambeau' => 'NcrflambeauAdapter',
        'msticker' => 'MstickerAdapter',
    ];

    /**
     * @param string $productType
     * @return QuoteAdapterInterface
     * @throws InvalidArgumentException
     */
    public static function create($productType)
    {
        $className = self::$typeMap[$productType] ?? null;

        if ($className === null) {
            throw new InvalidArgumentException(
                "Unknown product type: '{$productType}'. Supported: " . implode(', ', self::getProductTypes())
            );
        }

        return new $className();
    }

    /**
     * @param string $productType
     * @return bool
     */
    public static function supports($productType)
    {
        return isset(self::$typeMap[$productType]);
    }

    /**
     * @return QuoteAdapterInterface[]
     */
    public static function getAll()
    {
        return [
            new InsertedAdapter(),
            new StickerAdapter(),
            new NamecardAdapter(),
            new EnvelopeAdapter(),
            new LittleprintAdapter(),
            new MerchandisebondAdapter(),
            new CadarokAdapter(),
            new NcrflambeauAdapter(),
            new MstickerAdapter(),
        ];
    }

    /**
     * @return string[]
     */
    public static function getProductTypes()
    {
        return [
            'sticker',
            'inserted',
            'namecard',
            'envelope',
            'littleprint',
            'merchandisebond',
            'cadarok',
            'ncrflambeau',
            'msticker',
        ];
    }

    /**
     * @return string[]
     */
    public static function getAliases()
    {
        return [
            'sticker_new' => 'sticker',
            'poster' => 'littleprint',
            'leaflet' => 'cadarok',
        ];
    }
}
