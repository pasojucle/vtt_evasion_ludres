import { useState } from 'react';

type AutocompleteProps = {
    list: any,
    value: any,
    label: string,
    className: string,
    placeholder: string,
    handleSelect: (itemObject: any)=>void,
    handleRemove: (itemObject?: any)=>void
}
import {
  Popover,
  PopoverTrigger,
  PopoverContent,
} from "@/components/ui/popover"
import {
  Command,
  CommandInput,
  CommandItem,
  CommandGroup,
  CommandEmpty,
} from "@/components/ui/command"
import { Check } from "lucide-react"
import { cn } from "@/lib/utils"

export default function Autocomplete({ list, value, handleSelect }: AutocompleteProps): React.JSX.Element {
  const [open, setOpen] = useState(false)
  const [textFilter, setTextFilter] = useState("")

  return (
    <Popover open={open} onOpenChange={setOpen}>
      <PopoverTrigger asChild>
        <input
          className="border px-2 py-1 w-full"
          placeholder="Choisir..."
          onFocus={() => setOpen(true)}
          onChange={(e) => setTextFilter(e.target.value)}
          value={textFilter}
        />
      </PopoverTrigger>
      <PopoverContent className="w-full p-0">
        <Command>
          <CommandInput placeholder="Rechercher..." />
          <CommandEmpty>Aucun résultat</CommandEmpty>
          <CommandGroup>
            {list.map((item: any) => (
              <CommandItem
                key={item.id}
                value={item}
                onSelect={() => {
                  handleSelect(item)
                  setTextFilter(item)
                  setOpen(false)
                }}
              >
                <Check
                  className={cn(
                    "mr-2 h-4 w-4",
                    value?.id === item.id ? "opacity-100" : "opacity-0"
                  )}
                />
                {item}
              </CommandItem>
            ))}
          </CommandGroup>
        </Command>
      </PopoverContent>
    </Popover>
  )
}
