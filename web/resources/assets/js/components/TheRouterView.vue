<template>
<div>
  <close></close>
  <router-view></router-view>
</div>
</template>
<script>
import close from '~/components/Close';
export default {
  components: {
    close
  }
}
</script>
