<?php
// components/Footer.php
?>
<footer class="bg-gray-800 text-white py-8">
  <div class="container mx-auto px-4">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
      <div>
        <h3 class="text-xl font-bold mb-4">Moyne Sam</h3>
        <p class="text-gray-300">
          Профессиональные клининговые услуги для вашего дома и офиса.
        </p>
      </div>
      
      <div>
        <h3 class="text-xl font-bold mb-4">Контакты</h3>
        <ul class="space-y-2 text-gray-300">
          <li class="flex items-center space-x-2">
            <i data-lucide="phone" class="w-5 h-5"></i>
            <span>+7 (900) 123-45-67</span>
          </li>
          <li class="flex items-center space-x-2">
            <i data-lucide="mail" class="w-5 h-5"></i>
            <span>info@moynesam.ru</span>
          </li>
          <li class="flex items-center space-x-2">
            <i data-lucide="map-pin" class="w-5 h-5"></i>
            <span>г. Москва, ул. Примерная, д. 123</span>
          </li>
        </ul>
      </div>
      
      <div>
        <h3 class="text-xl font-bold mb-4">Режим работы</h3>
        <p class="text-gray-300">
          Пн-Пт: 8:00 - 20:00<br />
          Сб-Вс: 9:00 - 18:00
        </p>
      </div>
    </div>
    
    <div class="mt-8 pt-6 border-t border-gray-700 text-center text-gray-400">
      <p>© <?= date('Y') ?> Moyne Sam. Все права защищены.</p>
    </div>
  </div>
</footer>

<script>
  // Инициализация иконок Lucide
  lucide.createIcons();
</script>