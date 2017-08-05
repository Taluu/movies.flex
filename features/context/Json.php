<?php
namespace App\Behat;

use InvalidArgumentException;

use Webmozart\Assert\Assert;
use Behapi\Context\Json as BehapiJson;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Behat\Gherkin\Node\PyStringNode;


class Json extends BehapiJson
{
    /** @Then in the json, :path should match :expect */
    public function in_the_json_path_should_match(string $path, string $expect)
    {
        Assert::regex($this->getValue($path), $expect);
    }

    /** @Then in the json, each :sub in :path should match :expect */
    public function in_the_json_path_each_sub_path_should_match(string $path, string $sub, string $match)
    {
        $accessor = PropertyAccess::createPropertyAccessor();

        foreach ($this->getValue($path) as $node) {
            Assert::regex($accessor->getValue($node, $sub), $match);
        }
    }

    /** @Then in the json, each element in :path should be sorted by :sub asc */
    public function in_the_json_path_each_element_should_be_sorted_by_sub_asc(string $path, string $sub)
    {
        $current = null;
        $accessor = PropertyAccess::createPropertyAccessor();

        foreach ($this->getValue($path) as $node) {
            try {
                if (null == $current) {
                    continue;
                }

                $nodeValue = $accessor->getValue($node, $sub);

                $compare = $current <=> $nodeValue;

                if (1 === $compare) {
                    throw new InvalidArgumentException("Wrong order, expected '{$current}' before '{$nodeValue}'");
                }
            } finally {
                $current = $accessor->getValue($node, $sub);
            }
        }
    }

    /** @Then in the json, each element in :path should be sorted by :sub desc */
    public function in_the_json_path_each_element_should_be_sorted_by_sub_desc(string $path, string $sub)
    {
        $current = null;
        $accessor = PropertyAccess::createPropertyAccessor();

        foreach ($this->getValue($path) as $node) {
            try {
                if (null == $current) {
                    continue;
                }

                $nodeValue = $accessor->getValue($node, $sub);

                $compare = $current <=> $nodeValue;

                if (-1 === $compare) {
                    throw new InvalidArgumentException("Wrong order, expected '{$current}' before '{$nodeValue}'");
                }
            } finally {
                $current = $accessor->getValue($node, $sub);
            }
        }
    }

    // bug I need to fix on behapi...
    public function theJsonPathShouldBePyString(string $path, PyStringNode $expected)
    {
        Assert::same($this->getValue($path), $expected->getRaw());
    }
}
