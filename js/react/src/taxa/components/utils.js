export function showItem(item) {
  const isArray = Array.isArray(item);
  return (!isArray && item !== '') || item.length > 0;
}
