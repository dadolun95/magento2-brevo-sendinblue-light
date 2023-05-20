<?php
/**
 * @package     Dadolun_SibContactSync
 * @copyright   Copyright (c) 2023 Dadolun (https://www.dadolun.com)
 * @license     Open Source License
 */

namespace Dadolun\SibContactSync\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Class InstallCountryCodes
 * @package Dadolun\SibContactSync\Setup\Patch\Data
 */
class InstallCountryCodes implements DataPatchInterface
{
    /** @var ModuleDataSetupInterface */
    private $moduleDataSetup;

    /**
     * InstallCountryCodes constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->moduleDataSetup->startSetup();
        $columns = ['iso_code', 'country_prefix', 'status'];
        $data = [['AD', '376', 1],
            ['AE', '971', 1],
            ['AF', '93', 1],
            ['AG', '1268', 1],
            ['AI', '1264', 1],
            ['AL', '355', 1],
            ['AM', '374', 1],
            ['AN', '599', 1],
            ['AO', '244', 1],
            ['AQ', '672', 1],
            ['AR', '54', 1],
            ['AS', '1684', 1],
            ['AT', '43', 1],
            ['AU', '61', 1],
            ['AW', '297', 1],
            ['AZ', '994', 1],
            ['BA', '387', 1],
            ['BB', '1246', 1],
            ['BD', '880', 1],
            ['BE', '32', 1],
            ['BF', '226', 1],
            ['BG', '359', 1],
            ['BH', '973', 1],
            ['BI', '257', 1],
            ['BJ', '229', 1],
            ['BL', '590', 1],
            ['BM', '1441', 1],
            ['BN', '673', 1],
            ['BO', '591', 1],
            ['BR', '55', 1],
            ['BS', '1242', 1],
            ['BT', '975', 1],
            ['BW', '267', 1],
            ['BY', '375', 1],
            ['BZ', '501', 1],
            ['CA', '1', 1],
            ['CC', '61', 1],
            ['CD', '242', 1],
            ['CF', '236', 1],
            ['CG', '242', 1],
            ['CH', '41', 1],
            ['CI', '225', 1],
            ['CK', '682', 1],
            ['CL', '56', 1],
            ['CM', '237', 1],
            ['CN', '86', 1],
            ['CO', '57', 1],
            ['CR', '506', 1],
            ['CU', '53', 1],
            ['CV', '238', 1],
            ['CX', '61', 1],
            ['CY', '357', 1],
            ['CZ', '420', 1],
            ['DE', '49', 1],
            ['DJ', '253', 1],
            ['DK', '45', 1],
            ['DM', '1767', 1],
            ['DO', '1809', 1],
            ['DZ', '213', 1],
            ['EC', '593', 1],
            ['EE', '372', 1],
            ['EG', '20', 1],
            ['EH', '212', 1],
            ['ER', '291', 1],
            ['ES', '34', 1],
            ['ET', '251', 1],
            ['FI', '358', 1],
            ['FJ', '679', 1],
            ['FK', '500', 1],
            ['FM', '691', 1],
            ['FO', '298', 1],
            ['FR', '33', 1],
            ['GA', '241', 1],
            ['GB', '44', 1],
            ['GD', '1473', 1],
            ['GE', '995', 1],
            ['GF', '594', 1],
            ['GH', '233', 1],
            ['GI', '350', 1],
            ['GL', '299', 1],
            ['GM', '220', 1],
            ['GN', '224', 1],
            ['GP', '590', 1],
            ['GQ', '240', 1],
            ['GR', '30', 1],
            ['GT', '502', 1],
            ['GU', '1671', 1],
            ['GW', '245', 1],
            ['GY', '592', 1],
            ['HK', '852', 1],
            ['HN', '504', 1],
            ['HR', '385', 1],
            ['HT', '509', 1],
            ['HU', '36', 1],
            ['ID', '62', 1],
            ['IE', '353', 1],
            ['IL', '972', 1],
            ['IM', '44', 1],
            ['IN', '91', 1],
            ['IO', '246', 1],
            ['IQ', '964', 1],
            ['IR', '98', 1],
            ['IS', '354', 1],
            ['IT', '39', 1],
            ['JE', '44', 1],
            ['JM', '1876', 1],
            ['JO', '962', 1],
            ['JP', '81', 1],
            ['KE', '254', 1],
            ['KG', '996', 1],
            ['KH', '855', 1],
            ['KI', '686', 1],
            ['KM', '269', 1],
            ['KN', '1869', 1],
            ['KP', '850', 1],
            ['KR', '82', 1],
            ['KW', '965', 1],
            ['KY', '1345', 1],
            ['KZ', '7', 1],
            ['LA', '856', 1],
            ['LB', '961', 1],
            ['LC', '1758', 1],
            ['LI', '423', 1],
            ['LK', '94', 1],
            ['LR', '231', 1],
            ['LS', '266', 1],
            ['LT', '370', 1],
            ['LU', '352', 1],
            ['LV', '371', 1],
            ['LY', '218', 1],
            ['MA', '212', 1],
            ['MC', '377', 1],
            ['MD', '373', 1],
            ['ME', '382', 1],
            ['MF', '1599', 1],
            ['MG', '261', 1],
            ['MH', '692', 1],
            ['MK', '389', 1],
            ['ML', '223', 1],
            ['MN', '976', 1],
            ['MO', '853', 1],
            ['MP', '1670', 1],
            ['MQ', '596', 1],
            ['MR', '222', 1],
            ['MS', '1664', 1],
            ['MT', '356', 1],
            ['MU', '230', 1],
            ['MV', '960', 1],
            ['MW', '265', 1],
            ['MX', '52', 1],
            ['MY', '60', 1],
            ['MZ', '258', 1],
            ['NA', '264', 1],
            ['NC', '687', 1],
            ['NE', '227', 1],
            ['NF', '672', 1],
            ['NG', '234', 1],
            ['NI', '505', 1],
            ['NL', '31', 1],
            ['NO', '47', 1],
            ['NP', '977', 1],
            ['NR', '674', 1],
            ['NU', '683', 1],
            ['NZ', '64', 1],
            ['OM', '968', 1],
            ['PA', '507', 1],
            ['PE', '51', 1],
            ['PF', '689', 1],
            ['PG', '675', 1],
            ['PH', '63', 1],
            ['PK', '92', 1],
            ['PL', '48', 1],
            ['PM', '508', 1],
            ['PN', '870', 1],
            ['PR', '1', 1],
            ['PS', '970', 1],
            ['PT', '351', 1],
            ['PW', '680', 1],
            ['PY', '595', 1],
            ['QA', '974', 1],
            ['RE', '262', 1],
            ['RO', '40', 1],
            ['RS', '381', 1],
            ['RU', '7', 1],
            ['RW', '250', 1],
            ['SA', '966', 1],
            ['SB', '677', 1],
            ['SC', '248', 1],
            ['SD', '249', 1],
            ['SE', '46', 1],
            ['SG', '65', 1],
            ['SH', '290', 1],
            ['SI', '386', 1],
            ['SJ', '47', 1],
            ['SK', '421', 1],
            ['SL', '232', 1],
            ['SM', '378', 1],
            ['SN', '221', 1],
            ['SO', '252', 1],
            ['SR', '597', 1],
            ['ST', '239', 1],
            ['SV', '503', 1],
            ['SY', '963', 1],
            ['SZ', '268', 1],
            ['TC', '1649', 1],
            ['TD', '235', 1],
            ['TF', '262', 1],
            ['TG', '228', 1],
            ['TH', '66', 1],
            ['TJ', '992', 1],
            ['TK', '690', 1],
            ['TL', '670', 1],
            ['TM', '993', 1],
            ['TN', '216', 1],
            ['TO', '676', 1],
            ['TR', '90', 1],
            ['TT', '1868', 1],
            ['TV', '688', 1],
            ['TW', '886', 1],
            ['TZ', '255', 1],
            ['UA', '380', 1],
            ['UG', '256', 1],
            ['UM', '1', 1],
            ['US', '1', 1],
            ['UY', '598', 1],
            ['UZ', '998', 1],
            ['VA', '39', 1],
            ['VC', '1784', 1],
            ['VE', '58', 1],
            ['VG', '1284', 1],
            ['VI', '1340', 1],
            ['VN', '84', 1],
            ['VU', '678', 1],
            ['WF', '681', 1],
            ['WS', '685', 1],
            ['YE', '967', 1],
            ['YT', '262', 1],
            ['ZA', '27', 1],
            ['ZM', '260', 1],
            ['ZW', '263', 1]];

        $this->moduleDataSetup->getConnection()
            ->insertArray($this->moduleDataSetup->getTable('sendinblue_country_codes'), $columns, $data);
        $this->moduleDataSetup->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '1.0.0';
    }
}
