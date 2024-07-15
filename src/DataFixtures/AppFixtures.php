<?php

namespace App\DataFixtures;

use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $product = new Product();
        $product->setProductName('Apple iPhone 13 Pro');
        $product->setProductDescription('The Apple iPhone 13 Pro is a smartphone that was designed, manufactured, and sold by Apple Inc.');
        $product->setProductPrice(999.99);
        $product->setProductImage('https://store.storeimages.cdn-apple.com/4982/as-images.apple.com/is/iphone-13-pro-family-hero?wid=940&hei=1112&fmt=jpeg&qlt=80&.v=1631659848000');
        $manager->persist($product);
        $manager->flush();
    }
}
