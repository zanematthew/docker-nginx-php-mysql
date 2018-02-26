<template>
  <div></div>
</template>
<script>
export default {
  mounted() {
    let location = JSON.parse(sessionStorage.getItem('location'));
    if ("geolocation" in navigator && _.isEmpty(location)) {
      console.log('Getting new location');
      navigator.geolocation.getCurrentPosition((pos) => {
        sessionStorage.setItem('location', JSON.stringify({
          lat: pos.coords.latitude,
          lon: pos.coords.longitude
        }));
      }, (err) => {
        console.warn(`ERROR(${err.code}): ${err.message}`);
      }, {
        enableHighAccuracy: true,
        timeout: 20000,
        maximumAge: 10000
      });
    }
  }
}
</script>
