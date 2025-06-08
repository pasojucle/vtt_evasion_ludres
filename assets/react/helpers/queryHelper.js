export const dataLoader = async (param) => {
  try {
    const res = await fetch(`/api/${param}`);
    const jsonResult = await res.json();

    return jsonResult;
  } catch (error) {
    
  }
};