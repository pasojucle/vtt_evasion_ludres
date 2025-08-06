export const idFromName = (name: string) => {
    const parts = name.split(/[\[\]]+/g).filter(function(p){return p!==''});
    return parts.join('_');
}