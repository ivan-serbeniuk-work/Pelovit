<script setup lang="ts">
// Нагороди були однією картинкою nagorods.png (1000×272) — її розтягувало
// на всю ширину контейнера (~1224px), тобто браузер збільшував оригінал,
// а на екранах 2x взагалі вдвічі. Тепер це чотири окремих файли, кожен
// показується меншим за свій натуральний розмір, тому лишається різким
// і будь-яку нагороду можна замінити окремо, не перемальовуючи стрічку.
//
// height беремо з натуральних розмірів файлів і множимо на спільний масштаб,
// щоб зберегти пропорції групи з макета: перша медаль більша за інші.
const awards = [
  { src: 'images/nagorody/leader_of_the_branch.png', alt: 'Leader of the Branch — Національний бізнес-рейтинг', h: 259 },
  { src: 'images/nagorody/lider_haluzi_2012.png', alt: 'Лідер галузі 2012 — Національний бізнес-рейтинг', h: 215 },
  { src: 'images/nagorody/kompaniya_roku_2018.png', alt: 'Компанія року 2018', h: 208 },
  { src: 'images/nagorody/znak_yakosti_2019.png', alt: 'Знак якості 100%, 2019', h: 218 },
]
</script>

<template>
  <div class="awards-row">
    <AppPicture
      v-for="award in awards"
      :key="award.src"
      :src="award.src"
      :alt="award.alt"
      img-class="awards-row__item"
      :style="{ '--award-h': `${award.h}px` }"
    />
  </div>
</template>
