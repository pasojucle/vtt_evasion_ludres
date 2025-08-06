export const imageUpload = async (file: File, token: string|undefined) => {
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
console.log('upload response', response)
    const result = await response.json();
    console.log('upload response', result)
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