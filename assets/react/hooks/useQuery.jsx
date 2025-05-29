export const useQuery = (param, data = null) => {
  const dataLoader = async (param) => {
    const res = await fetch(`/api/${param}`);
    const jsonResult = await res.json();

    return jsonResult;
  };

  return [dataLoader];
}