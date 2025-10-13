import React from 'react';
import { Save, CircleX } from 'lucide-react';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';

type SectionEditProps = {
    title: string;
    handleChangeTitle: (e: React.ChangeEvent<HTMLInputElement>) => void;
    handleSubmit: (e: React.FormEvent<HTMLFormElement>) => void;
    handleClose: (refresh:boolean) => void;
}

export default function SectionEdit({title, handleChangeTitle,  handleSubmit, handleClose }: SectionEditProps): React.JSX.Element | undefined {

    return (
        <form className="w-full" onSubmit={handleSubmit}>
            <div className="grid grid-cols-1">
                <Input type="text" variant="outline" name="chapter[title]" value={title} onInput={handleChangeTitle} required />
                <div className='flex justify-center mt-2' role='group'>
                    <div className='inline-flex rounded-md shadow-xs' role='group'>
                        <Button 
                            className='w-1/2 md:w-auto rounded-none rounded-s-lg' 
                            type="button" 
                            variant="outline" 
                            onClick={() => handleClose(false)}
                        >
                            <CircleX /> Annuler
                        </Button>
                        <Button className='w-1/2 md:w-auto rounded-none rounded-e-lg' type="submit"><Save /> Enregistrer</Button>
                    </div>
                </div>
            </div>
        </form>
    ) 
}