<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SaleOrder;
use App\Models\User;
use App\Models\Affiliate;
use Carbon\Carbon;

class SaleOrderSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🛒 Creando órdenes de venta españolas para la cooperativa energética...');
        
        // Limpiar órdenes existentes
        SaleOrder::query()->delete();
        
        $users = User::all();
        $affiliates = Affiliate::all();
        
        if ($users->isEmpty()) {
            $this->command->warn('⚠️ No hay usuarios disponibles. Creando órdenes sin usuario creador.');
            $users = collect([null]);
        }
        
        if ($affiliates->isEmpty()) {
            $this->command->warn('⚠️ No hay afiliados disponibles. Creando órdenes sin afiliado.');
            $affiliates = collect([null]);
        }
        
        $this->createEnergyProductOrders($users, $affiliates);
        $this->createSolarInstallationOrders($users, $affiliates);
        $this->createEfficiencyEquipmentOrders($users, $affiliates);
        $this->createBulkOrders($users, $affiliates);
        $this->createSubscriptionOrders($users, $affiliates);
        $this->createCustomProjectOrders($users, $affiliates);
        $this->createPreOrderOrders($users, $affiliates);
        $this->createWholesaleOrders($users, $affiliates);
        
        $this->command->info('✅ SaleOrderSeeder completado. Se crearon ' . SaleOrder::count() . ' órdenes de venta españolas.');
    }
    
    private function createEnergyProductOrders($users, $affiliates): void
    {
        $this->command->info('⚡ Creando órdenes de productos energéticos...');
        
        $energyProducts = [
            [
                'name' => 'Panel Solar Monocristalino 400W',
                'quantity' => 2,
                'unit_price' => 299.99,
                'category' => 'paneles-solares'
            ],
            [
                'name' => 'Batería de Litio 5kWh',
                'quantity' => 1,
                'unit_price' => 2499.99,
                'category' => 'baterias'
            ],
            [
                'name' => 'Inversor Híbrido 3kW',
                'quantity' => 1,
                'unit_price' => 899.99,
                'category' => 'inversores'
            ],
            [
                'name' => 'Termostato Inteligente WiFi',
                'quantity' => 3,
                'unit_price' => 89.99,
                'category' => 'eficiencia'
            ],
            [
                'name' => 'Aerogenerador Doméstico 1kW',
                'quantity' => 1,
                'unit_price' => 1599.99,
                'category' => 'eolica'
            ]
        ];
        
        for ($i = 0; $i < 25; $i++) {
            $product = $energyProducts[array_rand($energyProducts)];
            $user = $users->random();
            $affiliate = $affiliates->random();
            
            $subtotal = $product['quantity'] * $product['unit_price'];
            $taxAmount = $subtotal * 0.21; // IVA español 21%
            $shippingAmount = $this->calculateShippingAmount($subtotal);
            $discountAmount = $this->calculateDiscountAmount($subtotal, $affiliate);
            $totalAmount = $subtotal + $taxAmount + $shippingAmount - $discountAmount;
            
            $orderData = $this->generateOrderData($user, $affiliate, 'standard', $subtotal, $taxAmount, $shippingAmount, $discountAmount, $totalAmount);
            $orderData['order_items'] = [
                [
                    'name' => $product['name'],
                    'quantity' => $product['quantity'],
                    'unit_price' => $product['unit_price'],
                    'total' => $subtotal,
                    'category' => $product['category']
                ]
            ];
            
            SaleOrder::create($orderData);
        }
    }
    
    private function createSolarInstallationOrders($users, $affiliates): void
    {
        $this->command->info('☀️ Creando órdenes de instalaciones solares...');
        
        $solarPackages = [
            [
                'name' => 'Kit Solar Residencial 3kW',
                'items' => [
                    ['name' => 'Panel Solar 400W', 'quantity' => 8, 'unit_price' => 299.99],
                    ['name' => 'Inversor Híbrido 3kW', 'quantity' => 1, 'unit_price' => 899.99],
                    ['name' => 'Batería 5kWh', 'quantity' => 1, 'unit_price' => 2499.99],
                    ['name' => 'Sistema de Montaje', 'quantity' => 1, 'unit_price' => 399.99],
                    ['name' => 'Instalación Profesional', 'quantity' => 1, 'unit_price' => 1200.00]
                ]
            ],
            [
                'name' => 'Kit Solar Comercial 10kW',
                'items' => [
                    ['name' => 'Panel Solar 400W', 'quantity' => 25, 'unit_price' => 299.99],
                    ['name' => 'Inversor Comercial 10kW', 'quantity' => 1, 'unit_price' => 2499.99],
                    ['name' => 'Batería 20kWh', 'quantity' => 1, 'unit_price' => 8999.99],
                    ['name' => 'Sistema de Montaje Industrial', 'quantity' => 1, 'unit_price' => 1299.99],
                    ['name' => 'Instalación Industrial', 'quantity' => 1, 'unit_price' => 3500.00]
                ]
            ]
        ];
        
        for ($i = 0; $i < 15; $i++) {
            $package = $solarPackages[array_rand($solarPackages)];
            $user = $users->random();
            $affiliate = $affiliates->random();
            
            $subtotal = collect($package['items'])->sum(function($item) {
                return $item['quantity'] * $item['unit_price'];
            });
            
            $taxAmount = $subtotal * 0.21;
            $shippingAmount = 0; // Instalación en sitio
            $discountAmount = $this->calculateDiscountAmount($subtotal, $affiliate);
            $totalAmount = $subtotal + $taxAmount + $shippingAmount - $discountAmount;
            
            $orderData = $this->generateOrderData($user, $affiliate, 'custom', $subtotal, $taxAmount, $shippingAmount, $discountAmount, $totalAmount);
            $orderData['order_items'] = $package['items'];
            $orderData['special_instructions'] = 'Instalación solar completa con servicio profesional incluido.';
            $orderData['expected_delivery_date'] = now()->addDays(rand(30, 90));
            
            SaleOrder::create($orderData);
        }
    }
    
    private function createEfficiencyEquipmentOrders($users, $affiliates): void
    {
        $this->command->info('🔋 Creando órdenes de equipos de eficiencia...');
        
        $efficiencyProducts = [
            [
                'name' => 'Sistema de Domótica Completo',
                'quantity' => 1,
                'unit_price' => 899.99,
                'category' => 'domotica'
            ],
            [
                'name' => 'Aislamiento Térmico 100m²',
                'quantity' => 1,
                'unit_price' => 1299.99,
                'category' => 'aislamiento'
            ],
            [
                'name' => 'Calefacción por Bomba de Calor',
                'quantity' => 1,
                'unit_price' => 3499.99,
                'category' => 'calefaccion'
            ],
            [
                'name' => 'Iluminación LED Inteligente',
                'quantity' => 20,
                'unit_price' => 24.99,
                'category' => 'iluminacion'
            ]
        ];
        
        for ($i = 0; $i < 20; $i++) {
            $product = $efficiencyProducts[array_rand($efficiencyProducts)];
            $user = $users->random();
            $affiliate = $affiliates->random();
            
            $subtotal = $product['quantity'] * $product['unit_price'];
            $taxAmount = $subtotal * 0.21;
            $shippingAmount = $this->calculateShippingAmount($subtotal);
            $discountAmount = $this->calculateDiscountAmount($subtotal, $affiliate);
            $totalAmount = $subtotal + $taxAmount + $shippingAmount - $discountAmount;
            
            $orderData = $this->generateOrderData($user, $affiliate, 'standard', $subtotal, $taxAmount, $shippingAmount, $discountAmount, $totalAmount);
            $orderData['order_items'] = [
                [
                    'name' => $product['name'],
                    'quantity' => $product['quantity'],
                    'unit_price' => $product['unit_price'],
                    'total' => $subtotal,
                    'category' => $product['category']
                ]
            ];
            
            SaleOrder::create($orderData);
        }
    }
    
    private function createBulkOrders($users, $affiliates): void
    {
        $this->command->info('📦 Creando órdenes al por mayor...');
        
        $bulkProducts = [
            [
                'name' => 'Paneles Solares 400W (Lote)',
                'quantity' => 50,
                'unit_price' => 249.99,
                'category' => 'paneles-solares'
            ],
            [
                'name' => 'Baterías de Litio 5kWh (Lote)',
                'quantity' => 20,
                'unit_price' => 1999.99,
                'category' => 'baterias'
            ],
            [
                'name' => 'Inversores Híbridos 3kW (Lote)',
                'quantity' => 25,
                'unit_price' => 749.99,
                'category' => 'inversores'
            ]
        ];
        
        for ($i = 0; $i < 12; $i++) {
            $product = $bulkProducts[array_rand($bulkProducts)];
            $user = $users->random();
            $affiliate = $affiliates->random();
            
            $subtotal = $product['quantity'] * $product['unit_price'];
            $taxAmount = $subtotal * 0.21;
            $shippingAmount = $this->calculateBulkShippingAmount($subtotal);
            $discountAmount = $subtotal * 0.15; // 15% descuento por volumen
            $totalAmount = $subtotal + $taxAmount + $shippingAmount - $discountAmount;
            
            $orderData = $this->generateOrderData($user, $affiliate, 'bulk', $subtotal, $taxAmount, $shippingAmount, $discountAmount, $totalAmount);
            $orderData['order_items'] = [
                [
                    'name' => $product['name'],
                    'quantity' => $product['quantity'],
                    'unit_price' => $product['unit_price'],
                    'total' => $subtotal,
                    'category' => $product['category']
                ]
            ];
            $orderData['special_instructions'] = 'Pedido al por mayor con descuento por volumen aplicado.';
            $orderData['expected_delivery_date'] = now()->addDays(rand(45, 120));
            
            SaleOrder::create($orderData);
        }
    }
    
    private function createSubscriptionOrders($users, $affiliates): void
    {
        $this->command->info('🔄 Creando órdenes de suscripción...');
        
        $subscriptionServices = [
            [
                'name' => 'Mantenimiento Solar Mensual',
                'quantity' => 12,
                'unit_price' => 49.99,
                'category' => 'mantenimiento'
            ],
            [
                'name' => 'Monitoreo Energético Anual',
                'quantity' => 1,
                'unit_price' => 299.99,
                'category' => 'monitoreo'
            ],
            [
                'name' => 'Servicio Técnico Premium',
                'quantity' => 1,
                'unit_price' => 599.99,
                'category' => 'servicio-tecnico'
            ]
        ];
        
        for ($i = 0; $i < 18; $i++) {
            $service = $subscriptionServices[array_rand($subscriptionServices)];
            $user = $users->random();
            $affiliate = $affiliates->random();
            
            $subtotal = $service['quantity'] * $service['unit_price'];
            $taxAmount = $subtotal * 0.21;
            $shippingAmount = 0; // Servicios digitales
            $discountAmount = $this->calculateDiscountAmount($subtotal, $affiliate);
            $totalAmount = $subtotal + $taxAmount + $shippingAmount - $discountAmount;
            
            $orderData = $this->generateOrderData($user, $affiliate, 'subscription', $subtotal, $taxAmount, $shippingAmount, $discountAmount, $totalAmount);
            $orderData['order_items'] = [
                [
                    'name' => $service['name'],
                    'quantity' => $service['quantity'],
                    'unit_price' => $service['unit_price'],
                    'total' => $subtotal,
                    'category' => $service['category']
                ]
            ];
            $orderData['special_instructions'] = 'Servicio de suscripción renovable automáticamente.';
            $orderData['expected_delivery_date'] = now()->addDays(rand(1, 30));
            
            SaleOrder::create($orderData);
        }
    }
    
    private function createCustomProjectOrders($users, $affiliates): void
    {
        $this->command->info('🏗️ Creando órdenes de proyectos personalizados...');
        
        $customProjects = [
            [
                'name' => 'Instalación Solar Industrial 50kW',
                'description' => 'Proyecto personalizado para nave industrial con sistema de almacenamiento',
                'base_price' => 45000.00
            ],
            [
                'name' => 'Sistema Híbrido Solar-Eólico',
                'description' => 'Combinación de energía solar y eólica para finca rural',
                'base_price' => 35000.00
            ],
            [
                'name' => 'Microgrid Comunitario',
                'description' => 'Red eléctrica independiente para comunidad rural',
                'base_price' => 75000.00
            ]
        ];
        
        for ($i = 0; $i < 10; $i++) {
            $project = $customProjects[array_rand($customProjects)];
            $user = $users->random();
            $affiliate = $affiliates->random();
            
            $subtotal = $project['base_price'] + rand(5000, 15000); // Variación del precio
            $taxAmount = $subtotal * 0.21;
            $shippingAmount = 0; // Instalación en sitio
            $discountAmount = $this->calculateDiscountAmount($subtotal, $affiliate);
            $totalAmount = $subtotal + $taxAmount + $shippingAmount - $discountAmount;
            
            $orderData = $this->generateOrderData($user, $affiliate, 'custom', $subtotal, $taxAmount, $shippingAmount, $discountAmount, $totalAmount);
            $orderData['order_items'] = [
                [
                    'name' => $project['name'],
                    'quantity' => 1,
                    'unit_price' => $subtotal,
                    'total' => $subtotal,
                    'category' => 'proyecto-personalizado',
                    'description' => $project['description']
                ]
            ];
            $orderData['special_instructions'] = 'Proyecto personalizado con consultoría técnica incluida.';
            $orderData['expected_delivery_date'] = now()->addDays(rand(90, 180));
            $orderData['internal_notes'] = 'Proyecto complejo que requiere planificación detallada y aprobación técnica.';
            
            SaleOrder::create($orderData);
        }
    }
    
    private function createPreOrderOrders($users, $affiliates): void
    {
        $this->command->info('📋 Creando órdenes de pre-orden...');
        
        $preOrderProducts = [
            [
                'name' => 'Batería de Grafeno 10kWh (Nueva Tecnología)',
                'quantity' => 1,
                'unit_price' => 3999.99,
                'category' => 'baterias-avanzadas'
            ],
            [
                'name' => 'Panel Solar de Perovskita 500W',
                'quantity' => 4,
                'unit_price' => 399.99,
                'category' => 'paneles-avanzados'
            ],
            [
                'name' => 'Sistema de Hidrógeno Verde',
                'quantity' => 1,
                'unit_price' => 15000.00,
                'category' => 'hidrogeno'
            ]
        ];
        
        for ($i = 0; $i < 8; $i++) {
            $product = $preOrderProducts[array_rand($preOrderProducts)];
            $user = $users->random();
            $affiliate = $affiliates->random();
            
            $subtotal = $product['quantity'] * $product['unit_price'];
            $taxAmount = $subtotal * 0.21;
            $shippingAmount = $this->calculateShippingAmount($subtotal);
            $discountAmount = $subtotal * 0.10; // 10% descuento por pre-orden
            $totalAmount = $subtotal + $taxAmount + $shippingAmount - $discountAmount;
            
            $orderData = $this->generateOrderData($user, $affiliate, 'pre_order', $subtotal, $taxAmount, $shippingAmount, $discountAmount, $totalAmount);
            $orderData['order_items'] = [
                [
                    'name' => $product['name'],
                    'quantity' => $product['quantity'],
                    'unit_price' => $product['unit_price'],
                    'total' => $subtotal,
                    'category' => $product['category']
                ]
            ];
            $orderData['special_instructions'] = 'Pre-orden de producto en desarrollo. Entrega estimada en 6-12 meses.';
            $orderData['expected_delivery_date'] = now()->addDays(rand(180, 365));
            $orderData['internal_notes'] = 'Producto en fase de desarrollo. Mantener contacto con cliente para actualizaciones.';
            
            SaleOrder::create($orderData);
        }
    }
    
    private function createWholesaleOrders($users, $affiliates): void
    {
        $this->command->info('🏭 Creando órdenes mayoristas...');
        
        $wholesaleProducts = [
            [
                'name' => 'Distribuidor Mayorista - Paneles Solares',
                'description' => 'Suministro mayorista para distribuidores autorizados',
                'base_price' => 25000.00
            ],
            [
                'name' => 'Contratista - Kit de Instalación',
                'description' => 'Equipos para contratistas de instalación solar',
                'base_price' => 15000.00
            ],
            [
                'name' => 'Cooperativa Rural - Sistema Comunitario',
                'description' => 'Sistema energético para cooperativa rural',
                'base_price' => 35000.00
            ]
        ];
        
        for ($i = 0; $i < 12; $i++) {
            $wholesale = $wholesaleProducts[array_rand($wholesaleProducts)];
            $user = $users->random();
            $affiliate = $affiliates->random();
            
            $subtotal = $wholesale['base_price'] + rand(2000, 8000);
            $taxAmount = $subtotal * 0.21;
            $shippingAmount = $this->calculateBulkShippingAmount($subtotal);
            $discountAmount = $subtotal * 0.20; // 20% descuento mayorista
            $totalAmount = $subtotal + $taxAmount + $shippingAmount - $discountAmount;
            
            $orderData = $this->generateOrderData($user, $affiliate, 'wholesale', $subtotal, $taxAmount, $shippingAmount, $discountAmount, $totalAmount);
            $orderData['order_items'] = [
                [
                    'name' => $wholesale['name'],
                    'quantity' => 1,
                    'unit_price' => $subtotal,
                    'total' => $subtotal,
                    'category' => 'mayorista',
                    'description' => $wholesale['description']
                ]
            ];
            $orderData['special_instructions'] = 'Pedido mayorista con condiciones especiales de pago y entrega.';
            $orderData['expected_delivery_date'] = now()->addDays(rand(60, 150));
            $orderData['internal_notes'] = 'Cliente mayorista con condiciones especiales. Verificar crédito comercial.';
            
            SaleOrder::create($orderData);
        }
    }
    
    private function generateOrderData($user, $affiliate, $orderType, $subtotal, $taxAmount, $shippingAmount, $discountAmount, $totalAmount): array
    {
        $statuses = ['pending', 'confirmed', 'processing', 'shipped', 'delivered'];
        $paymentStatuses = ['pending', 'partial', 'paid'];
        $shippingStatuses = ['pending', 'processing', 'shipped', 'delivered'];
        $paymentMethods = ['credit_card', 'debit_card', 'paypal', 'bank_transfer', 'cash'];
        $shippingMethods = ['standard', 'express', 'pickup', 'local_delivery'];
        
        $status = $statuses[array_rand($statuses)];
        $paymentStatus = $paymentStatuses[array_rand($paymentStatuses)];
        $shippingStatus = $shippingStatuses[array_rand($shippingStatuses)];
        
        $paidAmount = $paymentStatus === 'paid' ? $totalAmount : 
                     ($paymentStatus === 'partial' ? $totalAmount * 0.5 : 0);
        
        $outstandingAmount = $totalAmount - $paidAmount;
        
        $orderData = [
            'order_number' => $this->generateOrderNumber(),
            'customer_id' => $user ? $user->id : 1,
            'affiliate_id' => $affiliate ? $affiliate->id : null,
            'order_type' => $orderType,
            'status' => $status,
            'payment_status' => $paymentStatus,
            'shipping_status' => $shippingStatus,
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'shipping_amount' => $shippingAmount,
            'discount_amount' => $discountAmount,
            'total_amount' => $totalAmount,
            'paid_amount' => $paidAmount,
            'refunded_amount' => 0.00,
            'outstanding_amount' => $outstandingAmount,
            'currency' => 'EUR',
            'exchange_rate' => 1.000000,
            'payment_method' => $paymentMethods[array_rand($paymentMethods)],
            'payment_reference' => $this->generatePaymentReference(),
            'payment_date' => $paymentStatus !== 'pending' ? now()->subDays(rand(1, 30)) : null,
            'shipping_method' => $shippingMethods[array_rand($shippingMethods)],
            'tracking_number' => $shippingStatus !== 'pending' ? $this->generateTrackingNumber() : null,
            'shipped_date' => in_array($shippingStatus, ['shipped', 'delivered']) ? now()->subDays(rand(1, 15)) : null,
            'delivered_date' => $shippingStatus === 'delivered' ? now()->subDays(rand(1, 7)) : null,
            'expected_delivery_date' => now()->addDays(rand(7, 30)),
            'shipping_address' => $this->generateSpanishAddress(),
            'billing_address' => $this->generateSpanishAddress(),
            'special_instructions' => $this->generateSpecialInstructions(),
            'internal_notes' => $this->generateInternalNotes(),
            'applied_discounts' => $discountAmount > 0 ? [
                [
                    'code' => 'DESCUENTO' . rand(10, 50),
                    'amount' => $discountAmount,
                    'type' => 'percentage'
                ]
            ] : null,
            'shipping_details' => [
                'carrier' => ['Correos Express', 'Seur', 'DHL', 'UPS'][array_rand([0, 1, 2, 3])],
                'service' => $shippingMethods[array_rand($shippingMethods)],
                'estimated_days' => rand(2, 7)
            ],
            'customer_notes' => [
                'preferred_delivery_time' => ['mañana', 'tarde', 'cualquier momento'][array_rand([0, 1, 2])],
                'special_requirements' => rand(0, 1) ? 'Llamar antes de entregar' : null
            ],
            'tags' => $this->generateTags($orderType),
            'created_by' => $user ? $user->id : 1,
            'processed_by' => $status !== 'pending' ? ($user ? $user->id : 1) : null,
            'shipped_by' => in_array($shippingStatus, ['shipped', 'delivered']) ? ($user ? $user->id : 1) : null,
            'delivered_by' => $shippingStatus === 'delivered' ? ($user ? $user->id : 1) : null,
        ];
        
        return $orderData;
    }
    
    private function generateOrderNumber(): string
    {
        // Generar un número único usando microtime y número aleatorio
        $microtime = microtime(true);
        $timestamp = substr(str_replace('.', '', $microtime), -8); // Últimos 8 dígitos
        $random = rand(100, 999);
        return 'SO-' . date('Y') . '-' . $timestamp . $random;
    }
    
    private function generatePaymentReference(): string
    {
        return 'PAY-' . strtoupper(substr(md5(uniqid()), 0, 8));
    }
    
    private function generateTrackingNumber(): string
    {
        return 'TRK-' . strtoupper(substr(md5(uniqid()), 0, 10));
    }
    
    private function generateSpanishAddress(): string
    {
        $streets = [
            'Calle Mayor', 'Avenida de la Constitución', 'Plaza de España', 'Calle Real',
            'Paseo de la Independencia', 'Calle del Pilar', 'Avenida de Goya', 'Calle de Alcalá'
        ];
        
        $cities = [
            'Zaragoza', 'Huesca', 'Teruel', 'Calatayud', 'Tarazona', 'Alcañiz', 'Fraga', 'Jaca'
        ];
        
        $provinces = ['Zaragoza', 'Huesca', 'Teruel'];
        
        $street = $streets[array_rand($streets)];
        $number = rand(1, 100);
        $city = $cities[array_rand($cities)];
        $province = $provinces[array_rand($provinces)];
        $postalCode = rand(22000, 50000);
        
        return "{$street}, {$number}\n{$city}, {$province}\n{$postalCode} España";
    }
    
    private function generateSpecialInstructions(): string
    {
        $instructions = [
            'Entregar en horario de mañana (9:00-13:00)',
            'Llamar 30 minutos antes de la entrega',
            'Entregar en recepción del edificio',
            'No dejar en la puerta, requiere firma',
            'Entregar en horario de tarde (15:00-19:00)',
            'Contactar con el conserje para acceso',
            'Entregar en la oficina principal',
            'Requiere identificación para la entrega'
        ];
        
        return $instructions[array_rand($instructions)];
    }
    
    private function generateInternalNotes(): string
    {
        $notes = [
            'Cliente preferente - prioridad alta',
            'Verificar stock antes de procesar',
            'Cliente nuevo - verificar documentación',
            'Pedido especial - atención especial',
            'Cliente recurrente - servicio premium',
            'Verificar disponibilidad de instaladores',
            'Cliente corporativo - condiciones especiales',
            'Pedido con descuento aplicado'
        ];
        
        return $notes[array_rand($notes)];
    }
    
    private function generateTags($orderType): array
    {
        $baseTags = ['energia-verde', 'sostenible', 'aragon'];
        
        $typeTags = [
            'standard' => ['estandar', 'producto'],
            'pre_order' => ['pre-orden', 'nueva-tecnologia'],
            'subscription' => ['suscripcion', 'servicio'],
            'wholesale' => ['mayorista', 'distribuidor'],
            'bulk' => ['por-mayor', 'volumen'],
            'custom' => ['personalizado', 'proyecto']
        ];
        
        return array_merge($baseTags, $typeTags[$orderType] ?? []);
    }
    
    private function calculateShippingAmount($subtotal): float
    {
        if ($subtotal >= 500) {
            return 0; // Envío gratis para pedidos grandes
        } elseif ($subtotal >= 200) {
            return 9.99; // Envío reducido
        } else {
            return 19.99; // Envío estándar
        }
    }
    
    private function calculateBulkShippingAmount($subtotal): float
    {
        if ($subtotal >= 10000) {
            return 0; // Envío gratis para pedidos muy grandes
        } elseif ($subtotal >= 5000) {
            return 49.99; // Envío reducido para mayoristas
        } else {
            return 99.99; // Envío estándar para mayoristas
        }
    }
    
    private function calculateDiscountAmount($subtotal, $affiliate): float
    {
        if (!$affiliate) {
            return 0;
        }
        
        $discountRate = $affiliate->commission_rate / 100; // Convertir porcentaje a decimal
        return $subtotal * ($discountRate * 0.1); // 10% del porcentaje de comisión como descuento
    }
}
