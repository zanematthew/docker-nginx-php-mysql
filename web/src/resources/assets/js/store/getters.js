'use strict';

// The primary model type being viewed.
// Only one can be viewed at a given time.
export const type = state => {
    return state.route.params.type;
};
