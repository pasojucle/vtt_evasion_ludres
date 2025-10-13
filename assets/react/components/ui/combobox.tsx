import { useState, useMemo, useEffect } from "react"
import { Check, ChevronsUpDown, Plus } from "lucide-react"
import { cn } from "@/lib/utils"
import {
    Command,
    CommandEmpty,
    CommandGroup,
    CommandInput,
    CommandItem,
} from "@/components/ui/command"
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from "@/components/ui/popover"
import { Button } from "@/components/ui/button"
import { Label } from "@/components/ui/label"

type Item = {
    label: string;
    value: string;
}

export type ComboboxItem = {
    id: number,
    name?: string,
    title?: string
}

type ComboboxProps = {
    label: string;
    items: ComboboxItem[];
    initialValue: number | string | undefined;
    className: string;
    placeholder: string;
    handleSelect: (item: string | undefined) => void;
    handleAddItem: (value: string) => void;
}

export function Combobox({label,  items, initialValue, className, placeholder, handleSelect, handleAddItem }: ComboboxProps) {
    const [open, setOpen] = useState(false);
    const [selectedItem, setSelectedItem] = useState<string>(String(initialValue));
    const [inputValue, setInputValue] = useState("");


    const normalizedItems = useMemo(() => {
        console.log('selectedItem', selectedItem);
        return items.map((item) => ({
            label: item.name ?? item.title ?? "",
            value: String(item.id),
        }))
    }, [items])

    // const handleAddItem = () => {
    //     console.log('handleAddItem', inputValue)
    // }

    return (
        <div className={className}>
            <Label>{label}</Label>
            <Popover open={open} onOpenChange={setOpen}>
                <PopoverTrigger asChild>
                    <Button
                        variant="outline"
                        role="combobox"
                        aria-expanded={open}
                        className="w-full justify-between"
                    >
                        <span className="overflow-hidden whitespace-nowrap text-ellipsis block">
                            {selectedItem
                            ? normalizedItems.find((item) => item.value === selectedItem)?.label
                            : placeholder}
                        </span>
                        <ChevronsUpDown className="ml-2 h-4 w-4 shrink-0 opacity-50" />
                    </Button>
                </PopoverTrigger>
                <PopoverContent className="w-full p-0">
                    <Command>
                        <CommandInput placeholder="Rechercher" value={inputValue} 
                                    onValueChange={(value) => {
                                        console.log('onChange', value, normalizedItems)
                                        setInputValue(value)
                                    }}/>
                        <CommandEmpty>
                            <div>Aucun résultat.</div>
                            {2 < inputValue.length &&
                                <Button className="mt-2" variant="outline" type="button" size="sm" onClick={() => {
                                        handleAddItem(inputValue);
                                        setSelectedItem("-1");
                                        setInputValue("")
                                        setOpen(false)
                                    }}>
                                    <Plus/> Ajouter « {inputValue} »
                                </Button>
                            }
                        </CommandEmpty>
                        <CommandGroup>
                            {normalizedItems.map((item) => (
                                <CommandItem
                                    key={item.value}
                                    value={item.value}
                                    onSelect={(currentValue) => {
                                        const selectValue = currentValue === selectedItem ? "" : currentValue
                                        console.log('command item on select', selectValue)
                                        setSelectedItem(selectValue)
                                        handleSelect(selectValue)
                                        setOpen(false)
                                    }}
                                >
                                    <Check
                                        className={cn(
                                            "mr-2 h-4 w-4",
                                            selectedItem === item.value
                                                ? "opacity-100"
                                                : "opacity-0"
                                        )}
                                    />
                                    {item.label}
                                </CommandItem>
                            ))}
                        </CommandGroup>
                    </Command>
                </PopoverContent>
            </Popover>
        </div>
    )
}
