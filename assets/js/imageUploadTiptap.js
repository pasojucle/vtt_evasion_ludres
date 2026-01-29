export const imageUpload = async (file, token) => {
  const formData = new FormData()
  formData.append('file', file)

  try {
    const response = await fetch('/api/uploads', {
      method: 'POST',
      body: formData,
        headers: {
            'Authorization': `Bearer ${token}`,
        }
    })
    const result = await response.json();
    if (result['@id']) {
        return decodeURIComponent(result['@id']);
    } else {
      alert('Erreur : URL manquante')
    }
  } catch (err) {
    console.error(err)
    alert('Échec de l’envoi')
  }
}