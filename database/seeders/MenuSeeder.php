<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\MenuItem;
use App\Models\Restaurant;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $restaurantId = 1;

        // Safety check to ensure the restaurant exists
        if (!Restaurant::find($restaurantId)) {
            $this->command->error("Restaurant ID {$restaurantId} not found. Please run RestaurantSetupSeeder first.");
            return;
        }

        // ======================
        // 1. Create Categories
        // ======================
        $categories = [
            ['id' => 1, 'name' => 'SOFT DRINK', 'sort_order' => 1],
            ['id' => 2, 'name' => 'MOCKTAIL', 'sort_order' => 2],
            ['id' => 3, 'name' => 'SOUP', 'sort_order' => 3],
            ['id' => 4, 'name' => 'VEG. STARTER', 'sort_order' => 4],
            ['id' => 5, 'name' => 'PAN. STARTER', 'sort_order' => 5],
            ['id' => 6, 'name' => 'FARSAN', 'sort_order' => 6],
            ['id' => 7, 'name' => 'CHAT', 'sort_order' => 7],
            ['id' => 8, 'name' => 'VEG. SUBJI', 'sort_order' => 8],
            ['id' => 9, 'name' => 'PAN. SUBJI', 'sort_order' => 9],
            ['id' => 10, 'name' => 'KOFTA SUBJI', 'sort_order' => 10],
            ['id' => 11, 'name' => 'KATHOL', 'sort_order' => 11],
            ['id' => 12, 'name' => 'GUJARATI SUBJI', 'sort_order' => 12],
            ['id' => 13, 'name' => 'ROTI', 'sort_order' => 13],
            ['id' => 14, 'name' => 'RICE', 'sort_order' => 14],
            ['id' => 15, 'name' => 'PAPAD', 'sort_order' => 15],
            ['id' => 16, 'name' => 'DAL', 'sort_order' => 16],
            ['id' => 17, 'name' => 'REG.SWEET', 'sort_order' => 17],
            ['id' => 18, 'name' => 'PREMIUM SWEET', 'sort_order' => 18],
            ['id' => 19, 'name' => 'ICE CREAM(Reg)', 'sort_order' => 19],
            ['id' => 20, 'name' => 'ICE CREAM(Pre)', 'sort_order' => 20],
        ];

        foreach ($categories as $catData) {
            Category::updateOrCreate(
                ['id' => $catData['id'], 'restaurant_id' => $restaurantId],
                [
                    'name' => $catData['name'],
                    'sort_order' => $catData['sort_order'],
                    'is_active' => true,
                ]
            );
        }

        // ======================
        // 2. Create Menu Items
        // ======================
        $menuItems = [
            // SOFT DRINK
            ['id' => 1, 'category_id' => 1, 'name' => 'Fanta', 'description' => 'Fanta', 'price' => 50.00, 'image_path' => 'restaurants/restaurant-1/Categories/soft-drink/fanta.jpg'],
            ['id' => 2, 'category_id' => 1, 'name' => 'Sprite', 'description' => 'Sprite', 'price' => 50.00, 'image_path' => 'restaurants/restaurant-1/Categories/soft-drink/sprite.jpg'],
            ['id' => 3, 'category_id' => 1, 'name' => 'Pepsi', 'description' => 'Pepsi', 'price' => 50.00, 'image_path' => 'restaurants/restaurant-1/Categories/soft-drink/pepsi.jpg'],

            // MOCKTAIL
            ['id' => 4, 'category_id' => 2, 'name' => 'Mint Mojito', 'description' => 'Mint Mojito', 'price' => 90.00, 'image_path' => 'restaurants/restaurant-1/Categories/mocktail/mint-mojito.jpg'],
            ['id' => 5, 'category_id' => 2, 'name' => 'Pina Colada', 'description' => 'Pina Colada', 'price' => 90.00, 'image_path' => 'restaurants/restaurant-1/Categories/mocktail/pina-colada.jpg'],
            ['id' => 6, 'category_id' => 2, 'name' => 'Fruit Punch', 'description' => 'Fruit Punch', 'price' => 90.00, 'image_path' => 'restaurants/restaurant-1/Categories/mocktail/fruit-punch.jpg'],
            ['id' => 7, 'category_id' => 2, 'name' => 'Blue Lagoon', 'description' => 'Blue Lagoon', 'price' => 90.00, 'image_path' => 'restaurants/restaurant-1/Categories/mocktail/blue-lagoon.png'],
            ['id' => 8, 'category_id' => 2, 'name' => 'Orange Blossom', 'description' => 'Orange Blossom', 'price' => 90.00, 'image_path' => 'restaurants/restaurant-1/Categories/mocktail/orange-blossom.jpg'],

            // SOUP
            ['id' => 9, 'category_id' => 3, 'name' => 'Cream of Tomato', 'description' => 'Cream Of Tomato', 'price' => 80.00, 'image_path' => 'restaurants/restaurant-1/Categories/soup/cream-of-tomato.jpg'],
            ['id' => 10, 'category_id' => 3, 'name' => 'Hot & Sour', 'description' => 'Hot & Sour', 'price' => 80.00, 'image_path' => 'restaurants/restaurant-1/Categories/soup/hot-sour.jpg'],
            ['id' => 11, 'category_id' => 3, 'name' => 'Manchow', 'description' => 'Manchow', 'price' => 80.00, 'image_path' => 'restaurants/restaurant-1/Categories/soup/manchow.jpg'],
            ['id' => 12, 'category_id' => 3, 'name' => 'Lemon Coriendor', 'description' => 'Lemon Coriendor', 'price' => 80.00, 'image_path' => 'restaurants/restaurant-1/Categories/soup/lemon-coriendor.jpg'],
            ['id' => 13, 'category_id' => 3, 'name' => 'Veg. Sweet Corn', 'description' => 'Veg Sweet Corn', 'price' => 80.00, 'image_path' => 'restaurants/restaurant-1/Categories/soup/veg-sweet-corn.jpg'],

            // VEG. STARTER
            ['id' => 14, 'category_id' => 4, 'name' => 'Veg. Lollipop', 'description' => 'Veg Lollipop', 'price' => 160.00, 'image_path' => 'restaurants/restaurant-1/Categories/veg-starter/veg-lollipop.jpg'],
            ['id' => 15, 'category_id' => 4, 'name' => 'Veg. Hara Bhara Kabab', 'description' => 'Veg Hara Bhara Kabab', 'price' => 160.00, 'image_path' => 'restaurants/restaurant-1/Categories/veg-starter/veg-hara-bhara-kabab.jpg'],
            ['id' => 16, 'category_id' => 4, 'name' => 'Veg. Spring Roll', 'description' => 'Veg Spring Roll', 'price' => 160.00, 'image_path' => 'restaurants/restaurant-1/Categories/veg-starter/veg-spring-roll.jpg'],
            ['id' => 17, 'category_id' => 4, 'name' => 'Chinese Cigar Roll', 'description' => 'Chinese Cigar Roll', 'price' => 160.00, 'image_path' => 'restaurants/restaurant-1/Categories/veg-starter/chinese-cigar-roll.jpeg'],
            ['id' => 18, 'category_id' => 4, 'name' => 'Veg. Crispy', 'description' => 'Veg Crispy', 'price' => 160.00, 'image_path' => 'restaurants/restaurant-1/Categories/veg-starter/veg-crispy.png'],
            ['id' => 19, 'category_id' => 4, 'name' => 'Veg. Manchurian', 'description' => 'Veg Manchurian', 'price' => 160.00, 'image_path' => 'restaurants/restaurant-1/Categories/veg-starter/veg-manchurian.jpg'],
            ['id' => 20, 'category_id' => 4, 'name' => 'Corn Tikki', 'description' => 'Corn Tikki', 'price' => 160.00, 'image_path' => 'restaurants/restaurant-1/Categories/veg-starter/corn-tikki.jpg'],

            // PAN. STARTER
            ['id' => 21, 'category_id' => 5, 'name' => 'Paneer Chilli Dry', 'description' => 'Paneer Chilli Dry', 'price' => 160.00, 'image_path' => 'restaurants/restaurant-1/Categories/pan-starter/paneer-chilli-dry.jpg'],
            ['id' => 22, 'category_id' => 5, 'name' => 'Panner Manchurian', 'description' => 'Panner Manchurian', 'price' => 160.00, 'image_path' => 'restaurants/restaurant-1/Categories/pan-starter/panner-manchurian.jpg'],
            ['id' => 23, 'category_id' => 5, 'name' => 'Paneer 65', 'description' => 'Paneer 65', 'price' => 160.00, 'image_path' => 'restaurants/restaurant-1/Categories/pan-starter/paneer-65.jpg'],

            // FARSAN
            ['id' => 24, 'category_id' => 6, 'name' => 'Mix Pakoda', 'description' => 'Mix Pakoda', 'price' => 120.00, 'image_path' => 'restaurants/restaurant-1/Categories/farsan/mix-pakoda.jpg'],
            ['id' => 25, 'category_id' => 6, 'name' => 'Lilva Kachori', 'description' => 'Lilva Kachori', 'price' => 120.00, 'image_path' => 'restaurants/restaurant-1/Categories/farsan/lilva-kachori.jpg'],
            ['id' => 26, 'category_id' => 6, 'name' => 'Khandvi', 'description' => 'Khandvi', 'price' => 120.00, 'image_path' => 'restaurants/restaurant-1/Categories/farsan/khandvi.jpg'],
            ['id' => 27, 'category_id' => 6, 'name' => 'Patra', 'description' => 'Patra', 'price' => 120.00, 'image_path' => 'restaurants/restaurant-1/Categories/farsan/patra.jpg'],
            ['id' => 28, 'category_id' => 6, 'name' => 'Khaman', 'description' => 'Khaman', 'price' => 120.00, 'image_path' => 'restaurants/restaurant-1/Categories/farsan/khaman.jpg'],
            ['id' => 29, 'category_id' => 6, 'name' => 'Cutlet', 'description' => 'Cutlet', 'price' => 120.00, 'image_path' => 'restaurants/restaurant-1/Categories/farsan/cutlet.jpg'],
            ['id' => 30, 'category_id' => 6, 'name' => 'Samosa', 'description' => 'Samosa', 'price' => 120.00, 'image_path' => 'restaurants/restaurant-1/Categories/farsan/samosa.jpg'],

            // CHAT
            ['id' => 31, 'category_id' => 7, 'name' => 'Pani Puri', 'description' => 'Pani Puri', 'price' => 120.00, 'image_path' => 'restaurants/restaurant-1/Categories/chat/pani-puri.jpg'],
            ['id' => 32, 'category_id' => 7, 'name' => 'Sev Puri', 'description' => 'Sev Puri', 'price' => 120.00, 'image_path' => 'restaurants/restaurant-1/Categories/chat/sev-puri.jpg'],
            ['id' => 33, 'category_id' => 7, 'name' => 'Papdi Chat', 'description' => 'Papdi Chat', 'price' => 120.00, 'image_path' => 'restaurants/restaurant-1/Categories/chat/papdi-chat.jpg'],
            ['id' => 34, 'category_id' => 7, 'name' => 'Basket Chat', 'description' => 'Basket Chat', 'price' => 120.00, 'image_path' => 'restaurants/restaurant-1/Categories/chat/basket-chat.jpg'],
            ['id' => 35, 'category_id' => 7, 'name' => 'Aloo Tikki Chat', 'description' => 'Aloo Tikki Chat', 'price' => 120.00, 'image_path' => 'restaurants/restaurant-1/Categories/chat/aloo-tikki-chat.jpg'],

            // VEG. SUBJI
            ['id' => 36, 'category_id' => 8, 'name' => 'Veg. Jaipuri (Brown)', 'description' => 'Veg Jaipuri Brown', 'price' => 180.00, 'image_path' => 'restaurants/restaurant-1/Categories/veg-subji/veg-jaipuri-brown.jpg'],
            ['id' => 37, 'category_id' => 8, 'name' => 'Veg. Handi', 'description' => 'Veg Handi', 'price' => 180.00, 'image_path' => 'restaurants/restaurant-1/Categories/veg-subji/veg-handi.jpg'],
            ['id' => 38, 'category_id' => 8, 'name' => 'Kadai (Brown)', 'description' => 'Kadai Brown', 'price' => 180.00, 'image_path' => 'restaurants/restaurant-1/Categories/veg-subji/kadai-brown.jpg'],
            ['id' => 39, 'category_id' => 8, 'name' => 'Veg. Makhanwala (Red)', 'description' => 'Veg Makhanwala Red', 'price' => 180.00, 'image_path' => 'restaurants/restaurant-1/Categories/veg-subji/veg-makhanwala-red.jpg'],
            ['id' => 40, 'category_id' => 8, 'name' => 'Veg. Hydrabadi (Green)', 'description' => 'Veg Hydrabadi Green', 'price' => 180.00, 'image_path' => 'restaurants/restaurant-1/Categories/veg-subji/veg-hydrabadi-green.jpg'],
            ['id' => 41, 'category_id' => 8, 'name' => 'Veg. Kolhapuri (Red)', 'description' => 'Veg Kolhapuri Red', 'price' => 180.00, 'image_path' => 'restaurants/restaurant-1/Categories/veg-subji/veg-kolhapuri-red.jpg'],
            ['id' => 42, 'category_id' => 8, 'name' => 'Veg. Mughlai (Brown)', 'description' => 'Veg Mughlai Brown', 'price' => 180.00, 'image_path' => 'restaurants/restaurant-1/Categories/veg-subji/veg-mughlai-brown.jpg'],
            ['id' => 43, 'category_id' => 8, 'name' => 'Veg. Toofani (Red)', 'description' => 'Veg Toofani Red', 'price' => 180.00, 'image_path' => 'restaurants/restaurant-1/Categories/veg-subji/veg-toofani-red.jpg'],

            // PAN. SUBJI
            ['id' => 44, 'category_id' => 9, 'name' => 'Paneer Tikka Masala (Red)', 'description' => 'Paneer Tikka Masala Red', 'price' => 180.00, 'image_path' => 'restaurants/restaurant-1/Categories/pan-subji/paneer-tikka-masala-red.jpg'],
            ['id' => 45, 'category_id' => 9, 'name' => 'Paneer Tawa Masala (Red)', 'description' => 'Paneer Tawa Masala Red', 'price' => 180.00, 'image_path' => 'restaurants/restaurant-1/Categories/pan-subji/paneer-tawa-masala-red.jpg'],
            ['id' => 46, 'category_id' => 9, 'name' => 'Paneer Kadai (Brown)', 'description' => 'Paneer Kadai Brown', 'price' => 180.00, 'image_path' => 'restaurants/restaurant-1/Categories/pan-subji/paneer-kadai-brown.jpg'],
            ['id' => 47, 'category_id' => 9, 'name' => 'Paneer Butter Masala (Red)', 'description' => 'Paneer Butter Masala Red', 'price' => 180.00, 'image_path' => 'restaurants/restaurant-1/Categories/pan-subji/paneer-butter-masala-red.jpg'],
            ['id' => 48, 'category_id' => 9, 'name' => 'Paneer Chatpata (Brown)', 'description' => 'Paneer Chatpata Brown', 'price' => 180.00, 'image_path' => 'restaurants/restaurant-1/Categories/pan-subji/paneer-chatpata-brown.jpg'],
            ['id' => 49, 'category_id' => 9, 'name' => 'Paneer Toofani (Red)', 'description' => 'Paneer Toofani Red', 'price' => 180.00, 'image_path' => 'restaurants/restaurant-1/Categories/pan-subji/paneer-toofani-red.jpg'],
            ['id' => 50, 'category_id' => 9, 'name' => 'Paneer Handi', 'description' => 'Paneer Handi', 'price' => 180.00, 'image_path' => 'restaurants/restaurant-1/Categories/pan-subji/paneer-handi.jpg'],
            ['id' => 51, 'category_id' => 9, 'name' => 'Balti (Red)', 'description' => 'Balti Red', 'price' => 180.00, 'image_path' => 'restaurants/restaurant-1/Categories/pan-subji/balti-red.jpg'],

            // KOFTA SUBJI
            ['id' => 52, 'category_id' => 10, 'name' => 'Malai', 'description' => 'Malai', 'price' => 180.00, 'image_path' => 'restaurants/restaurant-1/Categories/kofta-subji/malai.jpg'],
            ['id' => 53, 'category_id' => 10, 'name' => 'Kasmiri Kofta (White)', 'description' => 'Kasmiri Kofta White', 'price' => 180.00, 'image_path' => 'restaurants/restaurant-1/Categories/kofta-subji/kasmiri-kofta-white.jpg'],
            ['id' => 54, 'category_id' => 10, 'name' => 'Veg Kofta (Brown)', 'description' => 'Veg Kofta Brown', 'price' => 180.00, 'image_path' => 'restaurants/restaurant-1/Categories/kofta-subji/veg-kofta-brown.jpg'],
            ['id' => 55, 'category_id' => 10, 'name' => 'Nargisi Kofta (Green)', 'description' => 'Nargisi Kofta Green', 'price' => 180.00, 'image_path' => 'restaurants/restaurant-1/Categories/kofta-subji/nargisi-kofta-green.jpg'],
            ['id' => 56, 'category_id' => 10, 'name' => 'Paneer Kofta (Red)', 'description' => 'Paneer Kofta Red', 'price' => 180.00, 'image_path' => 'restaurants/restaurant-1/Categories/kofta-subji/paneer-kofta-red.jpg'],

            // KATHOL
            ['id' => 57, 'category_id' => 11, 'name' => 'Mug Masala', 'description' => 'Mug Masala', 'price' => 120.00, 'image_path' => 'restaurants/restaurant-1/Categories/kathol/mug-masala.jpg'],
            ['id' => 58, 'category_id' => 11, 'name' => 'Chana Masala', 'description' => 'Chana Masala', 'price' => 120.00, 'image_path' => 'restaurants/restaurant-1/Categories/kathol/chana-masala.jpg'],

            // GUJARATI SUBJI
            ['id' => 59, 'category_id' => 12, 'name' => 'Sev Tamate', 'description' => 'Sev Tamate', 'price' => 180.00, 'image_path' => 'restaurants/restaurant-1/Categories/gujarati-subji/sev-tamate.jpg'],
            ['id' => 60, 'category_id' => 12, 'name' => 'Bhindi Masala', 'description' => 'Bhindi Masala', 'price' => 180.00, 'image_path' => 'restaurants/restaurant-1/Categories/gujarati-subji/bhindi-masala.jpg'],
            ['id' => 61, 'category_id' => 12, 'name' => 'Lusaniya Bataka', 'description' => 'Lusaniya Bataka', 'price' => 180.00, 'image_path' => 'restaurants/restaurant-1/Categories/gujarati-subji/lusaniya-bataka.jpg'],
            ['id' => 62, 'category_id' => 12, 'name' => 'Rusawala Bataka Tomato', 'description' => 'Rusawala Bataka Tomato', 'price' => 180.00, 'image_path' => 'restaurants/restaurant-1/Categories/gujarati-subji/rusawala-bataka-tomato.jpg'],

            // ROTI
            ['id' => 63, 'category_id' => 13, 'name' => 'Butter Roti', 'description' => 'Butter Roti', 'price' => 30.00, 'image_path' => 'restaurants/restaurant-1/Categories/roti/butter-roti.jpg'],
            ['id' => 64, 'category_id' => 13, 'name' => 'Butter Naan', 'description' => 'Butter Naan', 'price' => 30.00, 'image_path' => 'restaurants/restaurant-1/Categories/roti/butter-naan.jpg'],
            ['id' => 65, 'category_id' => 13, 'name' => 'Chapati', 'description' => 'Chapati', 'price' => 30.00, 'image_path' => 'restaurants/restaurant-1/Categories/roti/chapati.jpg'],
            ['id' => 66, 'category_id' => 13, 'name' => 'Paratha', 'description' => 'Paratha', 'price' => 30.00, 'image_path' => 'restaurants/restaurant-1/Categories/roti/paratha.jpg'],
            ['id' => 67, 'category_id' => 13, 'name' => 'Butter Kulcha', 'description' => 'Butter Kulcha', 'price' => 30.00, 'image_path' => 'restaurants/restaurant-1/Categories/roti/butter-kulcha.jpg'],

            // RICE
            ['id' => 68, 'category_id' => 14, 'name' => 'Jeera Rice', 'description' => 'Jeera Rice', 'price' => 150.00, 'image_path' => 'restaurants/restaurant-1/Categories/rice/jeera-rice.jpg'],
            ['id' => 69, 'category_id' => 14, 'name' => 'Veg. Pulav', 'description' => 'Veg Pulav', 'price' => 150.00, 'image_path' => 'restaurants/restaurant-1/Categories/rice/veg-pulav.jpg'],
            ['id' => 70, 'category_id' => 14, 'name' => 'Peas Pulav', 'description' => 'Peas Pulav', 'price' => 150.00, 'image_path' => 'restaurants/restaurant-1/Categories/rice/peas-pulav.jpg'],
            ['id' => 71, 'category_id' => 14, 'name' => 'Biryani', 'description' => 'Biryani', 'price' => 150.00, 'image_path' => 'restaurants/restaurant-1/Categories/rice/biryani.jpg'],
            ['id' => 72, 'category_id' => 14, 'name' => 'Veg. Hydrabadi Biryani', 'description' => 'Veg Hydrabadi Biryani', 'price' => 150.00, 'image_path' => 'restaurants/restaurant-1/Categories/rice/veg-hydrabadi-biryani.jpg'],
            ['id' => 73, 'category_id' => 14, 'name' => 'Handi Dum Biryani', 'description' => 'Handi Dum Biryani', 'price' => 150.00, 'image_path' => 'restaurants/restaurant-1/Categories/rice/handi-dum-biryani.jpg'],

            // PAPAD
            ['id' => 74, 'category_id' => 15, 'name' => 'Roasted Papad', 'description' => 'Roasted Papad', 'price' => 40.00, 'image_path' => 'restaurants/restaurant-1/Categories/papad/roasted-papad.jpg'],
            ['id' => 75, 'category_id' => 15, 'name' => 'Fry Papad', 'description' => 'Fry Papad', 'price' => 40.00, 'image_path' => 'restaurants/restaurant-1/Categories/papad/fry-papad.jpg'],
            ['id' => 76, 'category_id' => 15, 'name' => 'Pum Pum Papad', 'description' => 'Pum Pum Papad', 'price' => 40.00, 'image_path' => 'restaurants/restaurant-1/Categories/papad/pum-pum-papad.jpg'],

            // DAL
            ['id' => 77, 'category_id' => 16, 'name' => 'Dal Fry', 'description' => 'Dal Fry', 'price' => 120.00, 'image_path' => 'restaurants/restaurant-1/Categories/dal/dal-fry.jpg'],
            ['id' => 78, 'category_id' => 16, 'name' => 'Tadka', 'description' => 'Tadka', 'price' => 120.00, 'image_path' => 'restaurants/restaurant-1/Categories/dal/tadka.jpg'],
            ['id' => 79, 'category_id' => 16, 'name' => 'Dal Palak', 'description' => 'Dal Palak', 'price' => 120.00, 'image_path' => 'restaurants/restaurant-1/Categories/dal/dal-palak.jpg'],

            // REG.SWEET
            ['id' => 80, 'category_id' => 17, 'name' => 'Gulab Jamun', 'description' => 'Gulab Jamun', 'price' => 120.00, 'image_path' => 'restaurants/restaurant-1/Categories/regsweet/gulab-jamun.jpg'],
            ['id' => 81, 'category_id' => 17, 'name' => 'Kala Jamun', 'description' => 'Kala Jamun', 'price' => 120.00, 'image_path' => 'restaurants/restaurant-1/Categories/regsweet/kala-jamun.jpg'],
            ['id' => 82, 'category_id' => 17, 'name' => 'Moong Dal Halwa', 'description' => 'Moong Dal Halwa', 'price' => 120.00, 'image_path' => 'restaurants/restaurant-1/Categories/regsweet/moong-dal-halwa.jpg'],
            ['id' => 83, 'category_id' => 17, 'name' => 'Gajar Halwa (Seasonal)', 'description' => 'Gajar Halwa Seasonal', 'price' => 120.00, 'image_path' => 'restaurants/restaurant-1/Categories/regsweet/gajar-halwa-seasonal.jpg'],
            ['id' => 84, 'category_id' => 17, 'name' => 'Dudhi Halwa', 'description' => 'Dudhi Halwa', 'price' => 120.00, 'image_path' => 'restaurants/restaurant-1/Categories/regsweet/dudhi-halwa.jpg'],
            ['id' => 85, 'category_id' => 17, 'name' => 'Mango Ras (Seasonal)', 'description' => 'Mango Ras Seasonal', 'price' => 120.00, 'image_path' => 'restaurants/restaurant-1/Categories/regsweet/mango-ras-seasonal.jpg'],

            // PREMIUM SWEET
            ['id' => 86, 'category_id' => 18, 'name' => 'Angoori Basundi', 'description' => 'Angoori Basundi', 'price' => 120.00, 'image_path' => 'restaurants/restaurant-1/Categories/premium-sweet/angoori-basundi.jpg'],
            ['id' => 87, 'category_id' => 18, 'name' => 'Kesar Pista Basundi', 'description' => 'Kesar Pista Basundi', 'price' => 120.00, 'image_path' => 'restaurants/restaurant-1/Categories/premium-sweet/kesar-pista-basundi.jpg'],
            ['id' => 88, 'category_id' => 18, 'name' => 'Sitafal  Basundi', 'description' => 'Sitafal  Basundi', 'price' => 120.00, 'image_path' => 'restaurants/restaurant-1/Categories/premium-sweet/sitafal-basundi.jpg'],
            ['id' => 89, 'category_id' => 18, 'name' => 'Anjeer Basundi', 'description' => 'Anjeer Basundi', 'price' => 120.00, 'image_path' => 'restaurants/restaurant-1/Categories/premium-sweet/anjeer-basundi.jpg'],
            ['id' => 90, 'category_id' => 18, 'name' => 'Srikhand', 'description' => 'Srikhand', 'price' => 120.00, 'image_path' => 'restaurants/restaurant-1/Categories/premium-sweet/srikhand.jpg'],
            ['id' => 91, 'category_id' => 18, 'name' => 'Mango Delight (Seasonal)', 'description' => 'Mango Delight Seasonal', 'price' => 120.00, 'image_path' => 'restaurants/restaurant-1/Categories/premium-sweet/mango-delight-seasonal.jpg'],

            // ICE CREAM(Reg)
            ['id' => 92, 'category_id' => 19, 'name' => 'Vanilla', 'description' => 'Vanilla', 'price' => 70.00, 'image_path' => 'restaurants/restaurant-1/Categories/ice-creamreg/vanilla.jpg'],
            ['id' => 93, 'category_id' => 19, 'name' => 'Kaju Draksh', 'description' => 'Kaju Draksh', 'price' => 70.00, 'image_path' => 'restaurants/restaurant-1/Categories/ice-creamreg/kaju-draksh.jpg'],
            ['id' => 94, 'category_id' => 19, 'name' => 'Strawberry', 'description' => 'Strawberry', 'price' => 70.00, 'image_path' => 'restaurants/restaurant-1/Categories/ice-creamreg/strawberry.jpg'],
            ['id' => 95, 'category_id' => 19, 'name' => 'Chocolate', 'description' => 'Chocolate', 'price' => 70.00, 'image_path' => 'restaurants/restaurant-1/Categories/ice-creamreg/chocolate.jpg'],
            ['id' => 96, 'category_id' => 19, 'name' => 'Vanilla With Hot Chocolate', 'description' => 'Vanilla With Hot Chocolate', 'price' => 70.00, 'image_path' => 'restaurants/restaurant-1/Categories/ice-creamreg/vanilla-with-hot-chocolate.jpg'],

            // ICE CREAM(Pre)
            ['id' => 97, 'category_id' => 20, 'name' => 'American Nuts', 'description' => 'American Nuts', 'price' => 100.00, 'image_path' => 'restaurants/restaurant-1/Categories/ice-creampre/american-nuts.jpg'],
            ['id' => 98, 'category_id' => 20, 'name' => 'Butterscotch', 'description' => 'Butterscotch', 'price' => 100.00, 'image_path' => 'restaurants/restaurant-1/Categories/ice-creampre/butterscotch.jpg'],
            ['id' => 99, 'category_id' => 20, 'name' => 'Rajbhog', 'description' => 'Rajbhog', 'price' => 100.00, 'image_path' => 'restaurants/restaurant-1/Categories/ice-creampre/rajbhog.jpg'],
            ['id' => 100, 'category_id' => 20, 'name' => 'Kesar Pista', 'description' => 'Kesar Pista', 'price' => 100.00, 'image_path' => 'restaurants/restaurant-1/Categories/ice-creampre/kesar-pista.jpg'],
        ];

        foreach ($menuItems as $itemData) {
            MenuItem::updateOrCreate(
                ['id' => $itemData['id'], 'restaurant_id' => $restaurantId],
                [
                    'category_id' => $itemData['category_id'],
                    'name' => $itemData['name'],
                    'description' => $itemData['description'],
                    'price' => $itemData['price'],
                    'image_path' => $itemData['image_path'],
                    'is_available' => true,
                ]
            );
        }

        $this->command->info('Categories and all 100 Menu Items seeded successfully with correct image paths!');
    }
}