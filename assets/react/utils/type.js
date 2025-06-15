export const idFromName = (name) => {
    const parts = name.split(/[\[\]]+/g).filter(function(p){return p!==''});
    return parts.join('_');
}