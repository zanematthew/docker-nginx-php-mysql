<template>
  <form v-on:submit.stop.prevent="addSchedule" class="form row is-item">
    <div class="grid is-85">
      <input
      type="text"
      placeholder="Schedule Name..."
      v-model="name"
      required
      class="name" />
    </div>

    <button
      class="grid is-10 no-padding"
      type="submit"
      v-bind:disabled="name == ''"
      ><icon name="plus"></icon></button>

  </form>
</template>
<script>
export default {
  data() {
    return {
      name: ''
    }
  },
  methods: {
    clearField() {
      return this.name = '';
    },
    addSchedule() {
      this.$store.dispatch('addSchedule', {
        name: this.name
      }).then(response => {
        this.clearField();
      });
    }
  }
}
</script>
<style lang="scss" scoped>
.no-padding {
  padding: 10px;
}
</style>