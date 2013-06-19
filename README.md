Doctrine Fixtures Loader
========================

Base loader for your project's object fixtures, with all their dependencies, using Doctrine and closures.

Introduction
------------

Typically in a real project that uses Doctrine, you have tons of entities related with each others that needs to be loaded during functional tests using `doctrine/data-fixtures` library. This base loader helps you to mantain a fixtures loader for your project that can give to the caller an instance of an object with all related dependencies loaded. See the example below.

Usage
-----

Suppose that your project has the following entities:

* Order
* Product
* Customer

Suppose that for every Order you have many Products and one Customer. Suppose that you need a fixture that loads an Order with a specific payment method. Suppose that your application requires that an Order must have all dependencies set. In this case, what you have to do is to instantiate an Order, **with all of its dependencies**, like as follows:

	<?php
	
	namespace MyProject\Tests\Fixtures;
	
	use Doctrine\Common\DataFixtures\AbstractFixture;
	use Doctrine\Common\Persistence\ObjectManager;
	
	class MyFixture extends AbstractFixture
	{
		public function load(ObjectManager $manager)
		{
			$customer = new Customer();
			// set Customer's properties
			
			$product1 = new Product();
			// set Product's properties
			
			$product2 = new Product();
			// set Product's properties
			
			$order = new Order();
			$order->setProducts(array($product1, $product2));
			$order->setCustomer($customer);
			// set other Order's properties
			
			$order->setPaymentMethod('my_specific_payment_method');
			
			$manager->persist($order);
			$manager->flush();
		}
	}

And that's ok but the code to build an Order with all of its dependencies will be probably repeated in every fixture that needs an Order. This is really uncomfortable, especially in a real project with much more entities and dependencies, because when you need an Order you don't want to worry about all of its dependencies; you just want an Order so you can set specific properties for the specific test case.

The solution proposed is to maintain a fixture loader (that extends this base loader) specific for your project as the following:

	<?php
	
	namespace MyProject\Tests\Fixtures;
	
	use Webgriffe\DoctrineFixturesLoader\Loader as BaseLoader;
	use MyProject\Entity\Order;
	use MyProject\Entity\Customer;
	use MyProject\Entity\Product;
	
	class Loader extends BaseLoader
	{
		public function loadCustomer($referenceName = 'default-customer', $forcePersist = true)
		{
			$objectLoader = function (
				Loader $loader
			) {
				$customer = new Customer();
				// set Customer's properties
				
				return $customer;
			};
			
			$this->load($referenceName, $forcePersist, $objectLoader);
		}
		
		public function loadProduct($referenceName = 'default-product', $forcePersist = true)
		{
			$objectLoader = function (
				Loader $loader
			) {
				$product = new Product();
				// set Product's properties
				
				return $product;
			};
			
			$this->load($referenceName, $forcePersist, $objectLoader);
		}
		
		public function loadOrder($referenceName = 'default-order', $forcePersist = true)
		{
			$objectLoader = function (
				Loader $loader
			) {		
				$order = new Order();
				$order->setProducts(
					array($loader->loadProduct(), $loader->loadProduct('another-product'))
				);
				$order->setCustomer($loader->loadCustomer());
				// set other Order's properties
				
				return $order;
			};
			
			$this->load($referenceName, $forcePersist, $objectLoader);
		}				
	}

Note that you can add any loader methods as you want, if needed. For example here we could add a `loadEmptyOrder()` method that creates an Order without Produts. So, with a fixture loader like the above one, the previous `MyFixture` become as follows:

	<?php
	
	namespace MyProject\Tests\Fixtures;
	
	use Doctrine\Common\DataFixtures\AbstractFixture;
	use Doctrine\Common\Persistence\ObjectManager;
	use MyProject\Tests\Fixtures\Loader;
	
	class MyFixture extends AbstractFixture
	{
		public function load(ObjectManager $manager)
		{
			$loader = new Loader($manager, $this->referenceRepository);
			$order = $loader->loadOrder();
			
			$order->setPaymentMethod('my_specific_payment_method');

			$manager->flush();
		}
	}
	
That's all.


Credits
-------

This base loader has been developed by [Webgriffe®](http://www.webgriffe.com). Please, report to us any bug or suggestion by GitHub issues.
	