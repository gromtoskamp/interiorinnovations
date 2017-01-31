<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Model\ResourceModel\Rule;

/**
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 * @magentoDataFixture Magento/SalesRule/_files/rules.php
 * @magentoDataFixture Magento/SalesRule/_files/coupons.php
 */
class CollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider setValidationFilterDataProvider()
     * @param string $couponCode
     * @param array $expectedItems
     */
    public function testSetValidationFilter($couponCode, $expectedItems)
    {
        $collection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\SalesRule\Model\ResourceModel\Rule\Collection'
        );
        $items = array_values($collection->setValidationFilter(1, 0, $couponCode)->getItems());

        $ids = [];
        foreach ($items as $key => $item) {
            $this->assertEquals($expectedItems[$key], $item->getName());
            if (in_array($item->getId(), $ids)) {
                $this->fail('Item should be unique in result collection');
            }
            $ids[] = $item->getId();
        }
    }

    public function setValidationFilterDataProvider()
    {
        return [
            'Check type COUPON' => ['coupon_code', ['#1', '#2', '#5']],
            'Check type NO_COUPON' => ['', ['#2', '#5']],
            'Check type COUPON_AUTO' => ['coupon_code_auto', ['#2', '#4', '#5']],
            'Check result with auto generated coupon' => ['autogenerated_3_1', ['#2', '#3', '#5']],
            'Check result with non actual previously generated coupon' => [
                'autogenerated_2_1',
                ['#2', '#5'],
            ],
            'Check result with wrong code' => ['wrong_code', ['#2', '#5']]
        ];
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/SalesRule/_files/rule_specific_date.php
     * @magentoConfigFixture general/locale/timezone Europe/Kiev
     */
    public function testMultiRulesWithTimezone()
    {
        $this->setSpecificTimezone('Europe/Kiev');
        $collection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\SalesRule\Model\ResourceModel\Rule\Collection'
        );
        $collection->addWebsiteGroupDateFilter(1, 0);
        $items = array_values($collection->getItems());
        $this->assertNotEmpty($items);
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/SalesRule/_files/rule_specific_date.php
     * @magentoConfigFixture general/locale/timezone Australia/Sydney
     */
    public function testMultiRulesWithDifferentTimezone()
    {
        $this->setSpecificTimezone('Australia/Sydney');
        $collection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\SalesRule\Model\ResourceModel\Rule\Collection'
        );
        $collection->addWebsiteGroupDateFilter(1, 0);
        $items = array_values($collection->getItems());
        $this->assertNotEmpty($items);
    }

    protected function setSpecificTimezone($timezone)
    {
        $localeData = [
            'section' => 'general',
            'website' => null,
            'store' => null,
            'groups' => [
                'locale' => [
                    'fields' => [
                        'timezone' => [
                            'value' => $timezone
                        ]
                    ]
                ]
            ]
        ];
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Config\Model\Config\Factory')
            ->create()
            ->addData($localeData)
            ->save();
    }

    public function tearDown()
    {
        // restore default timezone
        $this->setSpecificTimezone('America/Los_Angeles');
    }
}
