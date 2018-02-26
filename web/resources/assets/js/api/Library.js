'use strcit';
class Library {
    static getIndex(then) {
        return axios.get(`/api/user/library/`).then(({data}) => then(data));
    }

    static toggleItem(then, payload) {
        return axios.post(`/api/user/library/toggle/${payload.item_id}/${payload.item_type}/`, {
            item_id: payload.item_id,
            item_type: payload.item_type
        }).then(({data}) => then(data));
    }
}
export default Library;
