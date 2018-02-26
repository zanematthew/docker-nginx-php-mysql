<template>
  <div>
    <social-sharing :url="url" inline-template>
    <div class="content">
      <network network="facebook" class="grid is-100 row is-item link">
        <icon name="facebook-square" class="align-icon fb"></icon> Facebook
      </network>
      <network network="twitter" class="grid is-100 row is-item link">
        <icon name="twitter-square" class="align-icon twitter"></icon> Twitter
      </network>
    </div>
    </social-sharing>
  </div>
</template>
<script>
export default {
  data() {
    return {
      id: this.$route.params.id,
      item: {}
    }
  },
  computed: {
    url() {
      return `${window.location.origin}/browse/events/${this.id}/${this.item.slug}`;
    }
  },
  mounted() {
    axios.get(`/api/event/${this.$route.params.id}/`).then(response => {
      this.item = response.data
    });
  },
  metaInfo() {
    return {
      titleTemplate: 'Action >> Share | My BMX Events'
    }
  }
}
</script>
<style>
.fb {
  color: #3b5998;
  background: #fff;
}
.twitter {
  color: #1da1f2;
  background: #fff;
}
</style>