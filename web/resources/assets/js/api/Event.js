'use strict';
class Event {
  // https://babeljs.io/repl/#?babili=false&evaluate=true&lineWrap=false&presets=es2015%2Creact%2Cstage-2&targets=&browsers=&builtIns=false&debug=false&code=(%7Bdata%7D)%20%3D%3E%20then(data)
  static single(then, id) {
    return axios.get('/api/event/'+id+'/').then(({data}) => then(data));
  }

  static events(then, when, query) {
    when = when.replace('-', '_');
    return axios.get('/api/events?'+when+'=true', {
        params: query
    }).then(({data}) => then(data));
  }
}
export default Event;